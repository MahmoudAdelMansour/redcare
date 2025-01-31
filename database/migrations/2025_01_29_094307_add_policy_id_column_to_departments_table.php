<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policy_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policies_id')->constrained('policies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('department_id')->constrained('departments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_department');
    }

};
