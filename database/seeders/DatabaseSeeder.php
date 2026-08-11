<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            LocationSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Operador AgroLink',
            'phone' => '+258840000000',
            'email' => 'operador@agrolinkmz.co.mz',
            'role' => 'operator',
        ]);
    }
}
