<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name'     => 'Mr Admin',
            'email'    => 'admin@doorsoft.co',
            'phone'    => '01111111111',
            'role'     => 1, // Admin role
            'password' => Hash::make('123456'),
            'status'   => 'Active',
            'language' => 'en',
        ]);

        \App\Models\User::create([
            'name'     => 'Mr User',
            'email'    => 'user@doorsoft.co',
            'phone'    => '01111111112',
            'role'     => 2, // User role
            'password' => Hash::make('123456'),
            'status'   => 'Active',
            'language' => 'en',
        ]);
    }
}
