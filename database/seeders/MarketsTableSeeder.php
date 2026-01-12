<?php
namespace Database\Seeders;

use App\Models\Market;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarketsTableSeeder extends Seeder
{
    public function run()
    {
        // 1. Create 'Supermarket' Category if not exists
        $category = ServiceCategory::firstOrCreate(
            ['name' => 'سوبر ماركت'],
            ['icon' => 'shopping_cart', 'description' => 'طلبات السوبر ماركت والبقالة']
        );

        // 2. Create 'Metro Market'
        $market = Market::firstOrCreate(
            ['name' => 'Metro Market'],
            [
                'description' => 'أفضل المنتجات الطازجة والجودة العالية',
                'phone'       => '19019',
                'is_active'   => true,
            ]
        );

        // 3. Create a Market Staff User
        $phone = '01000000999';
        $staff = User::where('phone', $phone)->first();

        if (! $staff) {
            User::create([
                'name'            => 'مدير مترو ماركت',
                'phone'           => $phone,
                'password'        => Hash::make('12345678'),
                'role'            => 'provider',
                'market_id'       => $market->id,
                'service_type_id' => $category->id,
                'is_active'       => true,
                'rating_average'  => 5.0,
                'rating_count'    => 10,
                'compound_id'     => 1,
            ]);
        }
    }
}
