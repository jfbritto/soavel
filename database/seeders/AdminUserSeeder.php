<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@soavel.com.br'],
            [
                'name'     => 'Administrador',
                'email'    => 'admin@soavel.com.br',
                'password' => Hash::make('soavel@2024'),
            ]
        );
    }
}
