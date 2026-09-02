<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ep00116@pnc.gob.sv'],
            [
                'name' => 'Geovanny',
                'oni' => 'ep00116',
                'password' => '100504',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RolePermissionSeeder::class,
        ]);
    }
}
