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
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'must_change_password']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'must_change_password']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('must_change_password')->default(true)->nullable();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('must_change_password')->default(true)->nullable();
        });
    }
};
