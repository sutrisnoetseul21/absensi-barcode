<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('gender', ['L', 'P'])->nullable()->after('no_hp');
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('previous_school', 255)->nullable()->after('religion');
            $table->date('admission_date')->nullable()->after('previous_school');
            $table->string('admission_class', 20)->nullable()->after('admission_date');
            $table->string('family_status', 50)->nullable()->after('admission_class');
            $table->tinyInteger('child_order')->unsigned()->nullable()->after('family_status');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'religion',
                'previous_school',
                'admission_date',
                'admission_class',
                'family_status',
                'child_order',
            ]);
        });
    }
};
