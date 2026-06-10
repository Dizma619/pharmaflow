<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get attendance records
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with('employee.user');

            // Filter by employee
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->get('employee_id'));
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('attendance_date', [
                    $request->get('start_date'),
                    $request->get('end_date')
                ]);
            }

            // This month
            if ($request->boolean('this_month')) {
                $query->thisMonth();
            }

            // Order
            $query->orderBy('attendance_date', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 30);
            $attendance = $query->paginate($perPage);

            return response()->json([
                'message' => 'Data kehadiran berhasil diambil',
                'data' => $attendance
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->firstOrFail();

            // Check if already checked in today
            $existingAttendance = Attendance::where('employee_id', $employee->id)
                                           ->whereDate('attendance_date', today())
                                           ->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return response()->json([
                    'message' => 'Anda sudah check in hari ini',
                    'data' => $existingAttendance
                ], 422);
            }

            if ($existingAttendance) {
                // Update existing record with check in
                $existingAttendance->update([
                    'check_in' => now(),
                    'latitude_in' => $validated['latitude'],
                    'longitude_in' => $validated['longitude'],
                    'status' => 'hadir',
                ]);

                $attendance = $existingAttendance;
            } else {
                // Create new record
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'attendance_date' => today(),
                    'check_in' => now(),
                    'latitude_in' => $validated['latitude'],
                    'longitude_in' => $validated['longitude'],
                    'status' => 'hadir',
                ]);
            }

            // Notify
            Notification::create([
                'user_id' => $user->id,
                'type' => 'transaksi_sistem',
                'title' => 'Check In Berhasil',
                'message' => 'Anda berhasil check in pada ' . now()->format('H:i'),
                'data' => json_encode(['attendance_id' => $attendance->id])
            ]);

            return response()->json([
                'message' => 'Check in berhasil',
                'data' => $attendance
            ], 200);

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
     * Check out
     */
    public function checkOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->firstOrFail();

            $attendance = Attendance::where('employee_id', $employee->id)
                                   ->whereDate('attendance_date', today())
                                   ->firstOrFail();

            if (!$attendance->check_in) {
                return response()->json([
                    'message' => 'Anda belum check in hari ini'
                ], 422);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'message' => 'Anda sudah check out hari ini',
                    'data' => $attendance
                ], 422);
            }

            $attendance->update([
                'check_out' => now(),
                'latitude_out' => $validated['latitude'],
                'longitude_out' => $validated['longitude'],
            ]);

            // Notify
            Notification::create([
                'user_id' => $user->id,
                'type' => 'transaksi_sistem',
                'title' => 'Check Out Berhasil',
                'message' => 'Anda berhasil check out pada ' . now()->format('H:i'),
                'data' => json_encode(['attendance_id' => $attendance->id])
            ]);

            return response()->json([
                'message' => 'Check out berhasil',
                'data' => $attendance
            ], 200);

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
     * Get today attendance
     */
    public function today(Request $request)
    {
        try {
            $query = Attendance::with('employee.user')
                              ->whereDate('attendance_date', today());

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            $attendance = $query->orderBy('check_in', 'asc')->get();

            // Stats
            $presentCount = $attendance->where('status', 'hadir')->count();
            $absentCount = $attendance->where('status', 'alpha')->count();
            $sickCount = $attendance->where('status', 'sakit')->count();
            $leaveCount = $attendance->where('status', 'izin')->count();

            $totalEmployees = Employee::where('status', 'aktif')->count();

            return response()->json([
                'message' => 'Data kehadiran hari ini berhasil diambil',
                'data' => [
                    'attendance' => $attendance,
                    'summary' => [
                        'total_employees' => $totalEmployees,
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'sick' => $sickCount,
                        'leave' => $leaveCount,
                        'present_percentage' =>
                $totalEmployees > 0
                    ? round(
                        ($presentCount / $totalEmployees) * 100,
                        2
                    ) . '%'
                    : '0%',
                        ]
                    ]
                ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark attendance manually (admin/owner only)
     */
    public function mark(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'attendance_date' => 'required|date',
                'status' => 'required|in:hadir,izin,sakit,alpha',
                'notes' => 'nullable|string',
            ]);

            $attendance = Attendance::where('employee_id', $validated['employee_id'])
                                   ->whereDate('attendance_date', $validated['attendance_date'])
                                   ->first();

            if ($attendance) {
                $attendance->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? $attendance->notes,
                ]);
            } else {
                $attendance = Attendance::create([
                    'employee_id' => $validated['employee_id'],
                    'attendance_date' => $validated['attendance_date'],
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            return response()->json([
                'message' => 'Kehadiran berhasil dicatat',
                'data' => $attendance->load('employee.user')
            ], 200);

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
     * Get monthly attendance report
     */
    public function monthlyReport(Request $request)
    {
        try {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            $employees = Employee::where('status', 'aktif')
                                ->with('user')
                                ->get();

            $report = $employees->map(function ($employee) use ($month, $year) {
                $attendance = Attendance::where('employee_id', $employee->id)
                                       ->whereMonth('attendance_date', $month)
                                       ->whereYear('attendance_date', $year)
                                       ->get();

                return [
                    'employee' => $employee->load('user'),
                    'present' => $attendance->where('status', 'hadir')->count(),
                    'sick' => $attendance->where('status', 'sakit')->count(),
                    'leave' => $attendance->where('status', 'izin')->count(),
                    'absent' => $attendance->where('status', 'alpha')->count(),
                    'total_days' => $attendance->count(),
                ];
            });

            return response()->json([
                'message' => 'Laporan kehadiran bulanan berhasil diambil',
                'data' => [
                    'period' => [
                        'month' => $month,
                        'year' => $year,
                    ],
                    'report' => $report,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}