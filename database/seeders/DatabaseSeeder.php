<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Huynh trưởng
        User::factory()->create([
            'name' => 'Huynh Trưởng An',
            'username' => 'huynhtruongan',
            'email' => 'huynhtruong@tnttgxtm.com',
            'role' => 'huynh_truong',
            'rank' => 'Huynh trưởng cấp 1',
            'years_of_activity' => 5,
            'phone' => '0901234567',
            'dob' => '1995-05-15',
            'address' => 'Giáo xứ Trù Mật',
        ]);

        // 2. Seed Giáo lý sinh
        User::factory()->create([
            'name' => 'Nguyễn Thị Dung',
            'username' => 'dungnt',
            'email' => 'dungnt@tnttgxtm.com',
            'role' => 'giao_ly_sinh',
            'grade_level' => 'Cấp 2',
            'branch' => 'Thiếu',
            'parent_phone' => '0987654321',
            'dob' => '2010-10-20',
            'address' => 'Giáo họ A',
        ]);
    }
}
