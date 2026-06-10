<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Get all employees
     */
    public function index(Request $request)
    {
        try {
            $query = Employee::with('user', 'attendance');

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

            // Filter by position
            if ($request->has('position')) {
                $query->where('position', $request->get('position'));
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $employees = $query->paginate($perPage);

            // Add attendance this month
            $employees->each(function ($employee) {
                $employee->attendance_this_month = $employee->attendance()
                                                           ->thisMonth()
                                                           ->count();
                $employee->present_count = $employee->attendance()
                                                   ->thisMonth()
                                                   ->where('status', 'hadir')
                                                   ->count();
            });

            return response()->json([
                'message' => 'Data karyawan berhasil diambil',
                'data' => $employees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new employee
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
                'position' => 'required|string',
                'hire_date' => 'required|date',
                'birth_date' => 'nullable|date',
                'id_number' => 'nullable|string',
                'bank_account' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'province' => 'nullable|string',
                'postal_code' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Create user
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'kasir', // Default role for new employee
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'province' => $validated['province'] ?? null,
                    'postal_code' => $validated['postal_code'] ?? null,
                    'is_active' => true,
                ]);

                // Create employee record
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => 'EMP-' . strtoupper(date('YmdHis')),
                    'position' => $validated['position'],
                    'hire_date' => $validated['hire_date'],
                    'birth_date' => $validated['birth_date'] ?? null,
                    'id_number' => $validated['id_number'] ?? null,
                    'bank_account' => $validated['bank_account'] ?? null,
                    'base_salary' => $validated['base_salary'] ?? 0,
                    'status' => 'aktif',
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Karyawan berhasil dibuat',
                    'data' => $employee->load('user')
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get specific employee
     */
    public function show($id)
    {
        try {
            $employee = Employee::with([
                'user',
                'attendance' => function ($q) {
                    $q->orderBy('attendance_date', 'desc')->limit(30);
                }
            ])->findOrFail($id);

            $employee->attendance_this_month = $employee->attendance()
                                                       ->thisMonth()
                                                       ->count();

            return response()->json([
                'message' => 'Data karyawan berhasil diambil',
                'data' => $employee
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update employee
     */
    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::with('user')->findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string',
                'email' => 'nullable|email|unique:users,email,' . $employee->user_id,
                'phone' => 'nullable|string|unique:users,phone,' . $employee->user_id,
                'position' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'id_number' => 'nullable|string',
                'bank_account' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:aktif,tidak_aktif,cuti,resign',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'province' => 'nullable|string',
                'postal_code' => 'nullable|string',
            ]);

            // Update user data
            $employee->user->update([
                'name' => $validated['name'] ?? $employee->user->name,
                'email' => $validated['email'] ?? $employee->user->email,
                'phone' => $validated['phone'] ?? $employee->user->phone,
                'address' => $validated['address'] ?? $employee->user->address,
                'city' => $validated['city'] ?? $employee->user->city,
                'province' => $validated['province'] ?? $employee->user->province,
                'postal_code' => $validated['postal_code'] ?? $employee->user->postal_code,
            ]);

            // Update employee data
            $employee->update([
                'position' => $validated['position'] ?? $employee->position,
                'birth_date' => $validated['birth_date'] ?? $employee->birth_date,
                'id_number' => $validated['id_number'] ?? $employee->id_number,
                'bank_account' => $validated['bank_account'] ?? $employee->bank_account,
                'base_salary' => $validated['base_salary'] ?? $employee->base_salary,
                'status' => $validated['status'] ?? $employee->status,
            ]);

            return response()->json([
                'message' => 'Data karyawan berhasil diperbarui',
                'data' => $employee->load('user')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Delete employee
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::with('user')->findOrFail($id);

            // Just set status to resign instead of deleting
            $employee->update(['status' => 'resign']);
            $employee->user->update(['is_active' => false]);

            return response()->json([
                'message' => 'Karyawan berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Get employee attendance report
     */
    public function attendanceReport($id, Request $request)
    {
        try {
            $employee = Employee::findOrFail($id);
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            $attendance = Attendance::where('employee_id', $id)
                                   ->whereMonth('attendance_date', $month)
                                   ->whereYear('attendance_date', $year)
                                   ->orderBy('attendance_date', 'asc')
                                   ->get();

            // Calculate stats
            $presentCount = $attendance->where('status', 'hadir')->count();
            $sickCount = $attendance->where('status', 'sakit')->count();
            $leaveCount = $attendance->where('status', 'izin')->count();
            $absentCount = $attendance->where('status', 'alpha')->count();

            $totalWorkingDays = now()->daysInMonth;

            return response()->json([
                'message' => 'Laporan kehadiran berhasil diambil',
                'data' => [
                    'employee' => $employee->load('user'),
                    'period' => [
                        'month' => $month,
                        'year' => $year,
                    ],
                    'summary' => [
                        'total_working_days' => $totalWorkingDays,
                        'present' => $presentCount,
                        'sick' => $sickCount,
                        'leave' => $leaveCount,
                        'absent' => $absentCount,
                        'present_percentage' => round(($presentCount / $totalWorkingDays) * 100, 2) . '%',
                    ],
                    'details' => $attendance,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }
    }
}