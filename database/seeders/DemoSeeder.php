<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\Address;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $clienUser = User::updateOrcreate([
                'name'     => 'Client',
                'email'    => strtolower('client@system.io'),
                'phone'    => rand(11111111111, 99999999999),
                'document' => '00000000000',
            ], [
                'password' => bcrypt(123456789)
            ]);
            $clienUser->assignRole(UserRoles::CLIENT);
            $client = Client::factory()->create([
                'user_id' => $clienUser->id,
            ]);

            Address::factory()->count(5)->create([
                'client_id' => $client->id,
            ]);
        });
    }
}
