<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            ['username' => 'admin'],
            ['password' => 'admin']
        );
    }
}
