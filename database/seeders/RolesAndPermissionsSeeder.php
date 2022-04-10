<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=RolesAndPermissionsSeeder
     * @return void
     */
    public function run()
    {
        // DEFINE GUARD
        $guard_api = ['guard_name' => 'api'];

        //  ROLES
        $roleAdmin      = Role::updateOrCreate(['name' => UserRoles::SUPER_ADMIN], $guard_api);

        $adminPermissions   = [];

        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:list'],
            array_merge($guard_api, ['description' => 'Listar Perfis'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:show'],
            array_merge($guard_api, ['description' => 'Ver Detalhes de Perfis'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:create'],
            array_merge($guard_api, ['description' => 'Cadastrar Perfis'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:edit'],
            array_merge($guard_api, ['description' => 'Editar Perfis'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:delete'],
            array_merge($guard_api, ['description' => 'Excluir Perfis'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'roles:permissions'],
            array_merge($guard_api, ['description' => 'Listar Permissoes do Perfil'])
        );

        $roleAdmin->givePermissionTo($adminPermissions);
    }
}
