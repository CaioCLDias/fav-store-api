<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ccldias.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@ccldias.com', 
                'password' => bcrypt('p@ssword123'),
                'is_admin' => true
            ]
        );
    }
}
