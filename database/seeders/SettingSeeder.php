<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keys = [
            ['key' => 'welcome_hero_image', 'label' => 'Ảnh bìa trang chủ', 'value' => ''],
            ['key' => 'stat_doan_sinh', 'label' => 'Số lượng Đoàn Sinh', 'value' => '450+'],
            ['key' => 'stat_huynh_truong', 'label' => 'Số lượng Huynh Trưởng', 'value' => '32'],
            ['key' => 'stat_lop_giao_ly', 'label' => 'Số lượng Lớp Giáo Lý', 'value' => '12'],
            ['key' => 'stat_hoat_dong', 'label' => 'Số lượng Hoạt Động/Năm', 'value' => '20+'],
            ['key' => 'footer_link_giao_xu', 'label' => 'Link Giáo xứ', 'value' => 'https://www.facebook.com/giaoxuTruMat'],
            ['key' => 'footer_link_facebook', 'label' => 'Link Facebook', 'value' => '#'],
            ['key' => 'footer_link_lien_he', 'label' => 'Link Liên hệ', 'value' => '#'],
            ['key' => 'footer_link_tai_lieu', 'label' => 'Link Tài liệu', 'value' => '#'],
        ];

        foreach ($keys as $k) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $k['key']],
                ['label' => $k['label'], 'value' => $k['value']]
            );
        }

        $cats = ['Giáo Lý', 'Câu Chuyện Thiếu Nhi', 'Tài Liệu Huynh Trưởng'];
        foreach ($cats as $c) {
            \App\Models\PostCategory::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($c)],
                ['name' => $c]
            );
        }
    }
}
