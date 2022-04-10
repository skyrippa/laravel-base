<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::updateOrcreate([
            'name'  => 'Administrador',
            'email' => strtolower('admin@system.com'),
            'phone' => 75999711234,
        ], [
            'password' => bcrypt(123123123)
        ]);

        $admin->assignRole(UserRoles::SUPER_ADMIN);
    }
}
