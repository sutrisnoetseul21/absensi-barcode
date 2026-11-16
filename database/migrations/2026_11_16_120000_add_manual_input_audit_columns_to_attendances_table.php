<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('manual_input_at')->nullable()->after('manual_input_by_type');
            $table->foreignUuid('manual_input_user_id')->nullable()->after('manual_input_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manual_input_user_id');
            $table->dropColumn('manual_input_at');
        });
    }
};

