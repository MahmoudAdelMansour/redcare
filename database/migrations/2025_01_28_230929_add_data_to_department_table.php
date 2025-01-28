<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // code , head ( related to user ) , goals ,main responsibilities , location , contact information
            $table->string('code')->nullable();
            $table->foreignId('user_id')->nullable()
                ->nullable()
                ->constrained('users');
            $table->longText('goals')->nullable();
            $table->longText('main_responsibilities')->nullable();


        });
    }

    public function down(): void
    {
        Schema::table('department', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('user_id');
            $table->dropColumn('goals');
            $table->dropColumn('main_responsibilities');
        });
    }
};
