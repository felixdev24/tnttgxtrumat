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
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change(); // Allow email to be nullable
            $table->string('username')->unique()->after('id');
            $table->string('role')->default('giao_ly_sinh')->after('username'); // 'huynh_truong' or 'giao_ly_sinh'
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('rank')->nullable(); // Cấp bậc
            $table->integer('years_of_activity')->nullable(); // Số năm hoạt động
            $table->string('grade_level')->nullable(); // Học sinh giáo lý cấp mấy
            $table->string('branch')->nullable(); // Ngành (Ấu, Thiếu, Nghĩa, Hiệp)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change(); // Revert back to non-nullable if needed
            $table->dropColumn([
                'username', 'role', 'avatar', 'address', 'dob', 'phone',
                'parent_phone', 'rank', 'years_of_activity', 'grade_level', 'branch',
            ]);
        });
    }
};
