<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Tin tức Giáo xứ',
            'Hoạt động TNTT',
            'Gương sáng Giáo lý',
            'Thông báo đoàn',
        ];

        foreach ($categories as $name) {
            PostCategory::query()->firstOrCreate(['name' => $name]);
        }
    }
}
