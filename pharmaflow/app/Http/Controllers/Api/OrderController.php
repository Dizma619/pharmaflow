<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Helpers\DistanceHelper;
use App\Services\DeliveryFeeService;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([

                'customer_id' =>
                    'nullable|exists:users,id',

                'customer_name' =>
                    'required|string',

                'customer_phone' =>
                    'required|string',

                'delivery_address' =>
                    'required|string',

                'delivery_latitude' =>
                    'required|numeric',

                'delivery_longitude' =>
                    'required|numeric',

                'delivery_city' =>
                    'nullable|string',

                'notes' =>
                    'nullable|string',

                'shipping_method' =>
                    'required|in:cod,online_payment',

                'items' =>
                    'required|array|min:1',

                'items.*.medicine_id' =>
                    'required|exists:medicines,id',

                'items.*.quantity' =>
                    'required|integer|min:1',

            ]);

            /*
            |--------------------------------------------------------------------------
            | APOTEK LOCATION
            |--------------------------------------------------------------------------
            */

            $pharmacyLat = -6.2088;
            $pharmacyLng = 106.8456;

            /*
            |--------------------------------------------------------------------------
            | DISTANCE
            |--------------------------------------------------------------------------
            */

            $distance =
                DistanceHelper::calculate(
                    $pharmacyLat,
                    $pharmacyLng,
                    $validated[
                        'delivery_latitude'
                    ],
                    $validated[
                        'delivery_longitude'
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | MAX DISTANCE
            |--------------------------------------------------------------------------
            */

            if ($distance > 10) {

                return response()->json([

                    'message' =>
                        'Lokasi terlalu jauh. Maksimal pengiriman 10 km'

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | SHIPPING COST
            |--------------------------------------------------------------------------
            */

            $shippingCost =
                DeliveryFeeService::calculate(
                    $distance
                );

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item[
                            'medicine_id'
                        ]
                    );

                $subtotal +=
                    $medicine->selling_price
                    *
                    $item['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $total =
                $subtotal
                +
                $shippingCost;

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */

            $order = Order::create([

                'order_number' =>
                    'ORD-' .
                    now()->format('Ymd')
                    . '-'
                    . strtoupper(
                        Str::random(5)
                    ),

                'customer_id' =>
                    $validated[
                        'customer_id'
                    ] ?? 1,

                'subtotal' =>
                    $subtotal,

                'shipping_cost' =>
                    $shippingCost,

                'delivery_distance_km' =>
                    $distance,

                'discount_amount' =>
                    0,

                'total_amount' =>
                    $total,

                'status' =>
                    'pending',

                'shipping_method' =>
                    $validated[
                        'shipping_method'
                    ],

                'delivery_address' =>
                    $validated[
                        'delivery_address'
                    ],

                'delivery_latitude' =>
                    $validated[
                        'delivery_latitude'
                    ],

                'delivery_longitude' =>
                    $validated[
                        'delivery_longitude'
                    ],

                'delivery_city' =>
                    $validated[
                        'delivery_city'
                    ] ?? null,

                'notes' =>
                    $validated[
                        'notes'
                    ] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item[
                            'medicine_id'
                        ]
                    );

                OrderItem::create([

                    'order_id' =>
                        $order->id,

                    'medicine_id' =>
                        $medicine->id,

                    'quantity' =>
                        $item[
                            'quantity'
                        ],

                    'price' =>
                        $medicine
                        ->selling_price,

                    'subtotal' =>
                        $medicine
                        ->selling_price
                        *
                        $item[
                            'quantity'
                        ],
                ]);
            }

            DB::commit();

            return response()->json([

                'message' =>
                    'Order berhasil dibuat',

                'order' =>
                    $order,

                'distance_km' =>
                    $distance,

                'shipping_cost' =>
                    $shippingCost,

                'total' =>
                    $total

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' =>
                    $e->getMessage()

            ], 500);
        }
    }
}