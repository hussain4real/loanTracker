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
        // User::factory(10)->withPersonalTeam()->create();

        // User::factory()->withPersonalTeam()->create([
        //     'name' => 'Super Admin',
        //     'email' => 'super@admin.com',
        //     'password' => bcrypt('password'),
        // ]);
        User::factory()->withPersonalTeam()->create([
            'name' => 'Khamis Al Ajmi',
            'email' => 'kalajmi@tamkeen-hq.com',
            'password' => bcrypt('password321'),
        ]);
    }
}
