<?php
namespace Database\Seeders;

use App\Models\Compound;
use Illuminate\Database\Seeder;

class CompoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Compound::create([
            'name'         => 'أجياد جاردن سيتي',
            'name_en'      => 'Agyad Garden City',
            'location_url' => 'https://maps.app.goo.gl/example', // User didn't provide URL, creating placeholder
            'address'      => '6 October City',
            'is_active'    => true,
        ]);
    }
}
