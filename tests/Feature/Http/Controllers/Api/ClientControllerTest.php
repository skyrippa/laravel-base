<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\UserRoles;
use App\Models\Client;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected $user = null;
    protected const URL = '/api/clients';

    public function setUp(): void
    {
        parent::setUp();

        $role = UserRoles::SUPER_ADMIN;

        $this->user = User::factory()->create();

        Artisan::call('db:seed');

        $this->user->assignRole($role);
    }

    private const COLLECTION_STRUCTURE = [
        'data'  => [
            '*' => self::DATA_STRUCTURE,
        ],
        'links' => ['first', 'last', 'prev', 'next'],
        'meta'  => [
            'current_page',
            'last_page',
            'from',
            'to',
            'path',
            'per_page',
            'total'
        ]
    ];

    private const DATA_STRUCTURE = [
        'id',
        'user' => [
            'name', 'email', 'phone', 'legal_entity', 'document',
        ],
    ];

    /**
     * @test
     */
    public function unauthenticatedUsersCanNotAccess()
    {
        $index = $this->json('GET', self::URL);
        $index->assertStatus(401);

        $indexAll = $this->json('GET', self::URL . '/all');
        $indexAll->assertStatus(401);

        $store = $this->json('POST', self::URL);
        $store->assertStatus(401);

        $show = $this->json('GET', self::URL . '/-1');
        $show->assertStatus(401);

        $update = $this->json('PUT', self::URL . '/-1');
        $update->assertStatus(401);

        $delete = $this->json('DELETE', self::URL . '/-1');
        $delete->assertStatus(401);

        $restore = $this->json('PATCH', self::URL . '/-1/restore');
        $restore->assertStatus(401);

        $profile = $this->json('GET', self::URL . '/me');
        $profile->assertStatus(401);

        $audits = $this->json('GET', self::URL . '/-1/audits');
        $audits->assertStatus(401);
    }

    /**
     * @test
     */
    public function unauthorizedUsersCanNotAccess()
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $index = $this->json('GET', self::URL);
        $index->assertStatus(403);

        $indexAll = $this->json('GET', self::URL . '/all');
        $indexAll->assertStatus(403);

        $store = $this->json('POST', self::URL);
        $store->assertStatus(403);

        $show = $this->json('GET', self::URL . '/-1');
        $show->assertStatus(403);

        $update = $this->json('PUT', self::URL . '/-1');
        $update->assertStatus(403);

        $delete = $this->json('DELETE', self::URL . '/-1');
        $delete->assertStatus(403);

        $restore = $this->json('PATCH', self::URL . '/-1/restore');
        $restore->assertStatus(403);

        $audits = $this->json('GET', self::URL . '/-1/audits');
        $audits->assertStatus(403);
    }

    /**
     * @test
     */
    public function willFailWith404IfNotFound()
    {
        Passport::actingAs($this->user);

        $show = $this->json('GET', self::URL . '/-1');
        $show->assertStatus(404);

        $update = $this->json('PUT', self::URL . '/-1');
        $update->assertStatus(404);

        $delete = $this->json('DELETE', self::URL . '/-1');
        $delete->assertStatus(404);

        $restore = $this->json('PATCH', self::URL . '/-1/restore');
        $restore->assertStatus(404);

        $audits = $this->json('GET', self::URL . '/-1/audits');
        $audits->assertStatus(404);
    }

    /**
     * @test
     */
    public function canReturnPaginatedCollection()
    {
        $quantity = 10;

        $upperLimit = min(($quantity / 2), 20);

        Client::factory()->count($quantity)
            ->forUser()
            ->create();

        Client::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('clients', $quantity);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL);

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($upperLimit, 'data.*');
    }

    /**
     * @test
     */
    public function canReturnSoftDeletedPaginatedCollection()
    {
        $quantity = 10;

        Client::factory()->count($quantity)
            ->forUser()
            ->create();

        Client::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('clients', $quantity);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . '/all');

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($quantity, 'data.*');
    }

    /**
     * @test
     */
    public function canStore()
    {
        $data     = Client::factory()->make();
        $userData = User::factory()->make();

        $data->email    = $userData->email;
        $data->document = $userData->document;
        $data->phone    = $userData->phone;

        Passport::actingAs($this->user);

        $response = $this->json('POST', self::URL, $data->toArray());

        $response->assertStatus(201)
            ->assertJsonStructure(array_merge(self::DATA_STRUCTURE, ["password"]))
            ->assertJson([
                'name' => $data->name,
                'user' => [
                    'email' => $data->email,
                    'phone' => Helpers::sanitizeString($data->phone),
                    'name' => $data->name,
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'name' => $data->name,
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);
    }

    /**
     * @test
     */
    public function canShow()
    {
        $data = Client::factory()
            ->forUser()
            ->create();

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . "/$data->id");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($data->toArray());
    }

    /**
     * @test
     */
    public function willReturn422IfValidationFails()
    {
        $data = Client::factory()
            ->forUser()
            ->create();

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$data->id", [
            'name' => null,
        ]);

        $response->assertStatus(422);

        $data = Client::factory()
            ->forUser()
            ->make([
                'name' => null,
            ]);

        $response = $this->json('POST', self::URL, $data->toArray());

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function canUpdate()
    {
        $client = Client::factory()
            ->forUser()
            ->create();

        $data = Client::factory()
            ->forUser()
            ->make();

        $userData = User::factory()->make();

        $data->email    = $userData->email;
        $data->document = $userData->document;
        $data->phone    = $userData->phone;

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$client->id", $data->toArray());

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson([
                'name' => $data->name,
                'user' => [
                    'name'     => $data->name,
                    'email'    => $data->email,
                    'phone'    => Helpers::sanitizeString($data->phone),
                    'document' => Helpers::sanitizeString($data->document),
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'name' => $data->name,
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);
    }

    /**
     * @test
     */
    public function canDelete()
    {
        $data = Client::factory()
            ->forUser()
            ->create();

        Passport::actingAs($this->user);

        $response = $this->json('DELETE', self::URL . "/$data->id");

        $response->assertStatus(204)
            ->assertSee(null);

        $this->assertSoftDeleted('clients', [
            'id' => $data->id
        ]);
    }

    /**
     * @test
     */
    public function canRestore()
    {
        $data = Client::factory()
            ->forUser()
            ->create();

        $data->delete();

        Passport::actingAs($this->user);

        $response = $this->json('PATCH', self::URL . "/$data->id/restore");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($data->makeHidden(['deleted_at', 'updated_at',])->toArray());

        $this->assertDatabaseHas('clients', [
            "id"   => $data->id,
            "name" => $data->name,
        ]);
    }
}
