<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\UserRoles;
use App\Models\Address;
use App\Models\City;
use App\Models\Client;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $client;
    protected $city;
    protected $state;
    protected const URL = '/api/addresses';

    public function setUp(): void
    {
        parent::setUp();

        $role = UserRoles::CLIENT;

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->state = State::factory()->create();
        $this->city = City::factory()->create([
            'state_id' => $this->state->id,
        ]);

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
        'client_id',
        'zip_code',
        'street',
        'house_number',
        'neighborhood',
        'complement',
        'observation',
        'phone',
        'state_id',
        'city_id',
        'client' => [
            'name',
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

        $profile = $this->json('GET', self::URL . '/me');
        $profile->assertStatus(403);

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

        Address::factory()->count($quantity)
            ->create([
                'client_id' => $this->client->id,
            ]);

        Address::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('addresses', $quantity);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL);

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($upperLimit, 'data.*');


        // limit
        $limit    = 2;
        $response = $this->json('GET', self::URL, [
            'limit' => $limit,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($limit, 'data.*');


        // FILTERS
        $data   = Address::first();
        $wheres = "where[]=id,$data->id";

        $response = $this->json('GET', self::URL . "?$wheres");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $data->id,
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data');

        // where
        $wheres = "where[]=street,$data->street";

        $response = $this->json('GET', self::URL . "?$wheres");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $data->id,
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data');


        // like
        $data  = Address::latest('id')->first();
        $likes = "like[]=street,$data->street";

        $response = $this->json('GET', self::URL . "?$likes");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $data->id,
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data');


        // between
        $date    = $data->created_at->format('Y-m-d');
        $between = "between[]=created_at,$date,$date";

        $response = $this->json('GET', self::URL . "?$between");

        $response->assertStatus(200)
            ->assertJsonCount($quantity / 2, 'data');


        // order by id desc
        $data  = Address::latest('id')->first();
        $query = "order=id,desc";

        $response = $this->json('GET', self::URL . "?$query");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $data->id
                    ]
                ]
            ])
            ->assertJsonCount($quantity / 2, 'data');


        // search with order by
        $query = "order=id,asc";

        $partner1 = Address::first();
        $query    = $query . "&search[]=id,$partner1->id";

        $partner2 = Address::latest('id')->first();
        $query    = $query . "&search[]=id,$partner2->id";

        $response = $this->json('GET', self::URL . "?$query");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $partner1->id
                    ],
                    1 => [
                        'id' => $partner2->id
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');


        //search with relationship
        Address::factory()
            ->create([
                'client_id' => $this->client->id,
            ]);

        $name = Address::first()->client->name;
        $query = "search[]=client.name,$name";

        $response = $this->json('GET', self::URL . "?$query");

        $response->assertStatus(200)
            ->assertJsonCount($quantity / 2 + 1, 'data');


        // various filters
        $date  = Address::first()->created_at->format('Y-m-d');
        $query = "between[]=created_at,$date,$date";

        $data  = Address::latest('id')->first();
        $query = $query . "&like[]=street,$data->street";
        $query = $query . "&where[]=id,$data->id";

        $response = $this->json('GET', self::URL . "?$query");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $data->id,
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function canReturnSoftDeletedPaginatedCollection()
    {
        $quantity = 10;

        Address::factory()->count($quantity)
            ->create([
                'client_id' => $this->client->id,
            ]);

        Address::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('addresses', $quantity);

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
        $data = Address::factory()->make([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
        ]);

        Passport::actingAs($this->user);

        $response = $this->json('POST', self::URL, $data->toArray());

        $response->assertStatus(201)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson([
                'client_id' => $this->client->id,
                'zip_code' => $data->zip_code,
                'street' => $data->street,
                'house_number' => $data->house_number,
                'neighborhood' => $data->neighborhood,
                'complement' => $data->complement,
                'observation' => $data->observation,
                'phone' => $data->phone,
                'state_id' => $data->state_id,
                'city_id' => $data->city_id,
                'state' => $this->state->toArray(),
                'city' => $this->city->toArray(),
                'client' => $this->client->toArray(),
            ]);

        $this->assertDatabaseHas('addresses', [
            'client_id' => $this->client->id,
            'zip_code' => $data->zip_code,
            'street' => $data->street,
            'house_number' => $data->house_number,
            'neighborhood' => $data->neighborhood,
            'complement' => $data->complement,
            'observation' => $data->observation,
            'phone' => $data->phone,
            'state_id' => $data->state_id,
            'city_id' => $data->city_id,
        ]);
    }

    /**
     * @test
     */
    public function canShow()
    {
        $data = Address::factory()->create([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'client_id' => $this->client->id,
        ]);

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
        $data = Address::factory()
            ->create([
                'state_id' => $this->state->id,
                'city_id' => $this->city->id,
                'client_id' => $this->client->id,
            ]);

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$data->id", [
            'name' => null,
        ]);

        $response->assertStatus(422);

        $data = Address::factory()
            ->make([
                'street' => null,
            ]);

        $response = $this->json('POST', self::URL, $data->toArray());

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function canUpdate()
    {
        $address = Address::factory()->create([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'client_id' => $this->client->id,
        ]);

        $data = Address::factory()
            ->make();

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$address->id", $data->toArray());

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson([
                'client_id' => $this->client->id,
                'zip_code' => $data->zip_code,
                'street' => $data->street,
                'house_number' => $data->house_number,
                'neighborhood' => $data->neighborhood,
                'complement' => $data->complement,
                'observation' => $data->observation,
                'phone' => $data->phone,
                'state_id' => $data->state_id,
                'city_id' => $data->city_id,
                'client' => $this->client->toArray(),
            ]);

        $this->assertDatabaseHas('addresses', [
            'client_id' => $this->client->id,
            'zip_code' => $data->zip_code,
            'street' => $data->street,
            'house_number' => $data->house_number,
            'neighborhood' => $data->neighborhood,
            'complement' => $data->complement,
            'observation' => $data->observation,
            'phone' => $data->phone,
            'state_id' => $data->state_id,
            'city_id' => $data->city_id,
        ]);
    }

    /**
     * @test
     */
    public function canDelete()
    {
        $data = Address::factory()->create([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'client_id' => $this->client->id,
        ]);

        Passport::actingAs($this->user);

        $response = $this->json('DELETE', self::URL . "/$data->id");

        $response->assertStatus(204)
            ->assertSee(null);

        $this->assertSoftDeleted('addresses', [
            'id' => $data->id
        ]);
    }

    /**
     * @test
     */
    public function canRestore()
    {
        $data = Address::factory()->create([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'client_id' => $this->client->id,
        ]);
        $data->delete();

        Passport::actingAs($this->user);

        $response = $this->json('PATCH', self::URL . "/$data->id/restore");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($data->makeHidden(['deleted_at', 'updated_at',])->toArray());

        $this->assertDatabaseHas('addresses', [
            "id" => $data->id,
        ]);
    }

    /**
     * @test
     */
    public function canGetAudits()
    {
        $data = Address::factory()->create([
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'client_id' => $this->client->id,
        ]);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . "/{$data->id}/audits");
        $audits   = $response->getData();

        $response->assertStatus(200)
            ->assertJson([
                0 => [
                    'id'         => $audits[0]->id,
                    'event'      => 'created',
                    'ip_address' => '127.0.0.1',
                    'old_values' => [],
                    'new_values' => [
                        'zip_code' => $data->zip_code,
                        'street' => $data->street,
                        'house_number' => $data->house_number,
                        'neighborhood' => $data->neighborhood,
                        'state_id' => $data->state_id,
                        'city_id' => $data->city_id,
                        'client_id' => $data->client_id,
                        'id' => $data->id,
                    ]
                ]
            ]);
    }
}
