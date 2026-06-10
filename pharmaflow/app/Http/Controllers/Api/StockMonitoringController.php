<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class StockMonitoringController extends Controller
{
    /**
     * CHECK EXPIRED MEDICINE
     * H-30 notification
     */
    public function checkExpiringMedicine()
    {
        try {

            $stocks = Stock::with('medicine')
                ->whereNotNull('expired_date')
                ->whereDate(
                    'expired_date',
                    '<=',
                    now()->addDays(30)
                )
                ->whereDate(
                    'expired_date',
                    '>=',
                    now()
                )
                ->where(
                    'quantity',
                    '>',
                    0
                )
                ->get();

            $created = 0;

            foreach ($stocks as $stock) {

                if (!$stock->medicine) {
                    continue;
                }

                $daysLeft =
                    now()->diffInDays(
                        $stock->expired_date,
                        false
                    );

                /**
                 * Prevent duplicate notification
                 */
                $alreadyExists =
                    Notification::where(
                        'type',
                        'expired_warning'
                    )
                    ->where(
                        'message',
                        'like',
                        '%' .
                        $stock->medicine->name .
                        '%'
                    )
                    ->whereDate(
                        'created_at',
                        today()
                    )
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $receivers =
                    User::whereIn(
                        'role',
                        ['owner', 'staff']
                    )->get();

                foreach (
                    $receivers
                    as $receiver
                ) {

                    Notification::create([

                        'user_id' =>
                            $receiver->id,

                        'title' =>
                            'Obat Akan Kadaluarsa',

                        'message' =>
                            $stock->medicine->name .
                            ' akan kadaluarsa dalam ' .
                            $daysLeft .
                            ' hari. Expired: ' .
                            Carbon::parse(
                                $stock->expired_date
                            )->format('d M Y'),

                        'type' =>
                            'expired_warning',

                        'is_read' =>
                            false
                    ]);

                    $created++;
                }
            }

            return response()->json([

                'success' => true,

                'message' =>
                    'Expired check completed',

                'notification_created' =>
                    $created
            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage()

            ], 500);
        }
    }
}