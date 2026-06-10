<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id')->unique();
            $table->string('position');
            $table->date('hire_date');
            $table->date('birth_date')->nullable();
            $table->string('id_number')->nullable(); // KTP/SIM
            $table->string('bank_account')->nullable();
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'cuti', 'resign'])->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}