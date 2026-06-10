<?php

namespace App\Services;

class DeliveryFeeService
{
    public static function calculate(
        float $distance
    ): int {

        if ($distance <= 2) {
            return 0;
        }

        if ($distance <= 5) {
            return 2000;
        }

        if ($distance <= 8) {
            return 4000;
        }

        if ($distance <= 10) {
            return 6000;
        }

        return 999999;
    }
}