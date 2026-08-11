<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Corredor piloto Dondo/Nhamatanda — Beira (secção 24 do business plan).
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Beira', 'district' => 'Beira', 'province' => 'Sofala', 'latitude' => -19.8437, 'longitude' => 34.8389],
            ['name' => 'Dondo', 'district' => 'Dondo', 'province' => 'Sofala', 'latitude' => -19.6103, 'longitude' => 34.7425],
            ['name' => 'Nhamatanda', 'district' => 'Nhamatanda', 'province' => 'Sofala', 'latitude' => -19.2975, 'longitude' => 34.7078],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['name' => $location['name']], $location);
        }
    }
}