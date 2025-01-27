<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_title')->nullable();
            $table->string('job_description')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('employee_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->string('status')->nullable();
            $table->string('extension_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
