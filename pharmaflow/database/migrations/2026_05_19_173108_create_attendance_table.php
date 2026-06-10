<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('latitude_in', 10, 8)->nullable();
            $table->decimal('longitude_in', 10, 8)->nullable();
            $table->decimal('latitude_out', 10, 8)->nullable();
            $table->decimal('longitude_out', 10, 8)->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}