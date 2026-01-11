<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'المشرف',
            'phone' => '0000000000',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Create sample resident
        User::create([
            'name' => 'أحمد محمد',
            'phone' => '0100000001',
            'password' => Hash::make('123456'),
            'role' => 'resident',
            'block_no' => 'A',
            'floor' => '4',
            'apt_no' => '402',
            'is_active' => true,
        ]);
        
        // Create service categories
        $categories = [
            ['name' => 'سباكة', 'name_en' => 'Plumbing', 'icon' => 'water_drop'],
            ['name' => 'كهرباء', 'name_en' => 'Electrical', 'icon' => 'electric_bolt'],
            ['name' => 'تنظيف', 'name_en' => 'Cleaning', 'icon' => 'cleaning_services'],
            ['name' => 'تكييف', 'name_en' => 'HVAC', 'icon' => 'ac_unit'],
            ['name' => 'نجارة', 'name_en' => 'Carpentry', 'icon' => 'carpenter'],
            ['name' => 'دهانات', 'name_en' => 'Painting', 'icon' => 'format_paint'],
            ['name' => 'صيانة عامة', 'name_en' => 'General Maintenance', 'icon' => 'build'],
            ['name' => 'أجهزة منزلية', 'name_en' => 'Appliances', 'icon' => 'kitchen'],
        ];
        
        foreach ($categories as $cat) {
            ServiceCategory::create([
                'name' => $cat['name'],
                'name_en' => $cat['name_en'],
                'icon' => $cat['icon'],
                'is_active' => true,
            ]);
        }
        
        // Create sample provider
        User::create([
            'name' => 'محمود السباك',
            'phone' => '0100000002',
            'password' => Hash::make('123456'),
            'role' => 'provider',
            'service_type_id' => 1, // Plumbing
            'is_active' => true,
        ]);
        
        User::create([
            'name' => 'علي الكهربائي',
            'phone' => '0100000003',
            'password' => Hash::make('123456'),
            'role' => 'provider',
            'service_type_id' => 2, // Electrical
            'is_active' => true,
        ]);
    }
}
