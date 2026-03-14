<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $email    = env('ADMIN_EMAIL', 'admin@admin.com');
        $password = env('ADMIN_PASSWORD', 'admin123');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Administrador',
                'email'    => $email,
                'password' => Hash::make($password),
            ]
        );
    }
}
