<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('email')->unique();

            $table->string('phone')->nullable();

            $table->string('password');

            // ROLE BARU
            $table->enum('role', [
                'owner',
                'staff',
                'customer'
            ])->default('customer');

            $table->text('address')->nullable();

            $table->string('city')->nullable();

            $table->string('province')->nullable();

            $table->string('postal_code')->nullable();

            $table->decimal(
                'latitude',
                12,
                8
            )->nullable();

            $table->decimal(
                'longitude',
                12,
                8
            )->nullable();

            $table->boolean(
                'is_active'
            )->default(true);

            $table->timestamp(
                'email_verified_at'
            )->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}