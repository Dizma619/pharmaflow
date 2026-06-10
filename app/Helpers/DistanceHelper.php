<?php

namespace App\Helpers;

class DistanceHelper
{
    public static function calculate(
        $lat1,
        $lon1,
        $lat2,
        $lon2
    ): float {

        $earthRadius = 6371;

        $latDelta =
            deg2rad(
                $lat2 - $lat1
            );

        $lonDelta =
            deg2rad(
                $lon2 - $lon1
            );

        $angle =

            sin(
                $latDelta / 2
            )

            *

            sin(
                $latDelta / 2
            )

            +

            cos(
                deg2rad($lat1)
            )

            *

            cos(
                deg2rad($lat2)
            )

            *

            sin(
                $lonDelta / 2
            )

            *

            sin(
                $lonDelta / 2
            );

        $c = 2 * atan2(
            sqrt($angle),
            sqrt(1 - $angle)
        );

        return round(
            $earthRadius * $c,
            2
        );
    }
}