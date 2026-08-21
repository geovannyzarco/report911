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
        User::create([
            'name' => 'Geovanny',
            'oni' => 'ep00116',
            'email' => 'ep00116@pnc.gob.sv',
            'password' => '100504',
            'email_verified_at' => now(),
        ]);
    }
}
