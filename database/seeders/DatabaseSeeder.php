<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Participant::count() === 0) {
            $names = ['Alice','Bob','Carol','Dave','Eve','Frank','Grace','Heidi'];
            foreach ($names as $name) {
                Participant::create([
                    'name' => $name,
                    'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                    'active' => true,
                ]);
            }
        }
    }
}
