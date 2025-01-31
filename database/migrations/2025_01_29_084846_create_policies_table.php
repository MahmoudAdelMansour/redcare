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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name')->nullable();
            $table->string('policy_number')->nullable();
            $table->text('description')->nullable();
            $table->string('purpose')->nullable();
            $table->string('version')->nullable();
            $table->text('details')->nullable();
            $table->string('link')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->nullable();
            $table->Text('compliance')->nullable();
            $table->text('notes')->nullable();
            $table->string('approval')->nullable();
            $table->foreignId('user_id')->constrained('users')
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
        Schema::dropIfExists('policies');
    }
};
