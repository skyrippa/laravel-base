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
        $roleAdmin = Role::updateOrCreate(['name' => UserRoles::SUPER_ADMIN], $guard_api);
        $roleClient = Role::updateOrCreate(['name' => UserRoles::CLIENT], $guard_api);

        $adminPermissions = [];
        $clientPermissions = [];

        // CLIENTS //
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:list'],
            array_merge($guard_api, ['description' => 'Listar Clientes'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:show'],
            array_merge($guard_api, ['description' => 'Ver Detalhes de Cliente'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:create'],
            array_merge($guard_api, ['description' => 'Cadastrar Clientes'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:edit'],
            array_merge($guard_api, ['description' => 'Editar Cliente'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:delete'],
            array_merge($guard_api, ['description' => 'Excluir Cliente'])
        );
        $adminPermissions[] = Permission::updateOrCreate(
            ['name' => 'clients:audits'],
            array_merge($guard_api, ['description' => 'Listar Auditoria de Clientes'])
        );

        // ADDRESSES //
        $adminPermissions[] = $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:list'],
            array_merge($guard_api, ['description' => 'Listar Endereços'])
        );
        $adminPermissions[] = $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:show'],
            array_merge($guard_api, ['description' => 'Ver Detalhes de Endereço'])
        );
        $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:create'],
            array_merge($guard_api, ['description' => 'Cadastrar Endereços'])
        );
        $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:edit'],
            array_merge($guard_api, ['description' => 'Editar Endereço'])
        );
        $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:delete'],
            array_merge($guard_api, ['description' => 'Excluir Endereço'])
        );
        $clientPermissions[] = Permission::updateOrCreate(
            ['name' => 'addresses:audits'],
            array_merge($guard_api, ['description' => 'Listar Auditoria de Endereços'])
        );

        $roleAdmin->givePermissionTo($adminPermissions);
        $roleClient->givePermissionTo($clientPermissions);
    }
}
