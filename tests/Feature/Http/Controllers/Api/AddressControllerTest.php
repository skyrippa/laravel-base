<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\SettingEnum;
use App\Enums\UserRoles;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected $user = null;
    protected const URL = '/api/addresses';

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
        'name',
        'user_id',
        'owner_name',
        'domain',
        'pix_key_type',
        'pix_merchant_name',
        'pix_merchant_city',
        'pix_key',
        'whatsapp',
        'user',
        'reserve_limit_hours',
    ];

    /**
     * @test
     */
    public function unauthenticatedUsersCanNotAccess()
    {
        $index = $this->json('GET', self::URL);
        $index->assertStatus(401);

        $index_all = $this->json('GET', self::URL . '/all');
        $index_all->assertStatus(401);

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

        $index_all = $this->json('GET', self::URL . '/all');
        $index_all->assertStatus(403);

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

        $upperLimit = ($quantity / 2) > 20 ? 20 : ($quantity / 2);

        Partner::factory()->count($quantity)
            ->forUser()
            ->create();

        Partner::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('partners', $quantity);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . '');

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($upperLimit, 'data.*');


        // limit
        $limit    = 2;
        $response = $this->json('GET', self::URL . '', [
            'limit' => $limit,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJsonCount($limit, 'data.*');


        // FILTERS
        $data   = Partner::first();
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
        $wheres = "where[]=name,$data->name";

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
        $data  = Partner::latest('id')->first();
        $likes = "like[]=name,$data->name";

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
        $data  = Partner::latest('id')->first();
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

        $partner1 = Partner::first();
        $query    = $query . "&search[]=id,$partner1->id";

        $partner2 = Partner::latest('id')->first();
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
        $user = User::factory()->create();

        $partner = Partner::factory()
            ->forUser()
            ->create();

        $email = Partner::first()->user->email;

        $query = "search[]=user.email,$email";

        $response = $this->json('GET', self::URL . "?$query");

        $response->assertStatus(200)
            ->assertJsonCount($quantity / 2 + 1, 'data');


        // various filters
        $date  = Partner::first()->created_at->format('Y-m-d');
        $query = "between[]=created_at,$date,$date";

        $data  = Partner::latest('id')->first();
        $query = $query . "&like[]=name,$data->name";
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
    public function canReturnSettingsOnIndex()
    {
        $partner = Partner::factory()->forUser()->create();
        $setting = Setting::factory()->create([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . '');

        $response->assertStatus(200)
            ->assertJsonStructure(self::COLLECTION_STRUCTURE)
            ->assertJson([
                'data' => [
                    0 => [
                        'id'       => $partner->id,
                        'settings' => [
                            $setting->key => $setting->value,
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.*');
    }

    /**
     * @test
     */
    public function canReturnSoftDeletedPaginatedCollection()
    {
        $quantity = 10;

        Partner::factory()->count($quantity)
            ->forUser()
            ->create();

        Partner::limit($quantity / 2)->delete();

        $this->assertDatabaseCount('partners', $quantity);

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
        $data     = Partner::factory()->make();
        $userData = User::factory()->make();

        $data->email    = $userData->email;
        $data->document = $userData->document;
        $data->phone    = $userData->phone;

        $data->settings          = Setting::convertBoolean(Setting::all());
        $nullKey                 = Helpers::genRandomString();
        $data->settings->nullKey = '';

        Passport::actingAs($this->user);

        $response = $this->json('POST', self::URL, $data->toArray());
        $body     = $response->getData();

        $response->assertStatus(201)
            ->assertJsonStructure(array_merge(self::DATA_STRUCTURE, ["password"]))
            ->assertJson([
                'name'                => $data->name,
                'owner_name'          => $data->owner_name,
                'domain'              => $data->domain,
                'pix_key_type'        => $data->pix_key_type,
                'pix_merchant_name'   => $data->pix_merchant_name,
                'pix_merchant_city'   => $data->pix_merchant_city,
                'pix_key'             => $data->pix_key,
                'whatsapp'            => Helpers::sanitizeString($data->whatsapp),
                'reserve_limit_hours' => 0,
                'user'                => [
                    'email' => $data->email,
                ],
            ]);

        $this->assertDatabaseHas('partners', [
            'name'                => $data->name,
            'owner_name'          => $data->owner_name,
            'domain'              => $data->domain,
            'pix_key_type'        => $data->pix_key_type,
            'pix_merchant_name'   => $data->pix_merchant_name,
            'pix_merchant_city'   => $data->pix_merchant_city,
            'pix_key'             => $data->pix_key,
            'whatsapp'            => Helpers::sanitizeString($data->whatsapp),
            'reserve_limit_hours' => 0,
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);

        foreach (Setting::partnerConfig() as $key => $value) {
            $this->assertDatabaseHas('settings', [
                'key'        => $key,
                'value'      => $value,
                'model_type' => Partner::class,
                'model_id'   => $body->id,
            ]);
        }
    }

    /**
     * @test
     */
    public function canStoreWithLogo()
    {
        $data     = Partner::factory()->make();
        $userData = User::factory()->make();

        $data->email               = $userData->email;
        $data->document            = $userData->document;
        $data->phone               = $userData->phone;
        $data->reserve_limit_hours = rand(1, 7);

        $data->settings['logo'] = TestCase::BASE64_EXAMPLE;

        Passport::actingAs($this->user);

        $response = $this->json('POST', self::URL, $data->toArray());
        $body     = $response->getData();

        $response->assertStatus(201)
            ->assertJsonStructure(array_merge(self::DATA_STRUCTURE, ["password"]))
            ->assertJson([
                'name'                => $data->name,
                'owner_name'          => $data->owner_name,
                'domain'              => $data->domain,
                'pix_key_type'        => $data->pix_key_type,
                'pix_merchant_name'   => $data->pix_merchant_name,
                'pix_merchant_city'   => $data->pix_merchant_city,
                'pix_key'             => $data->pix_key,
                'whatsapp'            => Helpers::sanitizeString($data->whatsapp),
                'reserve_limit_hours' => $data->reserve_limit_hours,
                'user'                => [
                    'email' => $data->email,
                ]
            ]);

        $this->assertDatabaseHas('partners', [
            'name'                => $data->name,
            'owner_name'          => $data->owner_name,
            'domain'              => $data->domain,
            'pix_key_type'        => $data->pix_key_type,
            'pix_merchant_name'   => $data->pix_merchant_name,
            'pix_merchant_city'   => $data->pix_merchant_city,
            'pix_key'             => $data->pix_key,
            'whatsapp'            => Helpers::sanitizeString($data->whatsapp),
            'reserve_limit_hours' => $data->reserve_limit_hours,
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);

        $this->assertDatabaseHas('settings', [
            'key'        => 'logo',
            'model_type' => Partner::class,
            'model_id'   => $body->id,
        ]);
    }

    /**
     * @test
     */
    public function canShow()
    {
        $data = Partner::factory()
            ->forUser()
            ->create();

        $setting = Setting::factory()->create([
            'model_type' => Partner::class,
            'model_id'   => $data->id,
        ]);

        Passport::actingAs($this->user);

        $response = $this->json('GET', self::URL . "/$data->id");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson(
                array_merge($data->toArray(), [
                    'settings' => [
                        $setting->key => $setting->value,
                    ]
                ])
            );
    }

    /**
     * @test
     */
    public function willReturn422IfValidationFails()
    {
        $data = Partner::factory()
            ->forUser()
            ->create();

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$data->id", [
            'name' => null,
        ]);

        $response->assertStatus(422);

        $data = Partner::factory()
            ->forUser()
            ->make([
                'name' => null,
            ]);

        $response = $this->json('POST', self::URL . "", $data->toArray());

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function canUpdate()
    {
        $partner = Partner::factory()
            ->forUser()
            ->create();

        $data = Partner::factory()
            ->forUser()
            ->make();

        $settings = Setting::factory()->count(2)->create([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);

        $data->settings = Setting::convertBoolean($settings);

        $userData = User::factory()->make();

        $data->email    = $userData->email;
        $data->document = $userData->document;
        $data->phone    = $userData->phone;

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$partner->id", $data->toArray());

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson([
                'name'              => $data->name,
                'owner_name'        => $data->owner_name,
                'domain'            => $data->domain,
                'pix_key_type'      => $data->pix_key_type,
                'pix_merchant_name' => $data->pix_merchant_name,
                'pix_merchant_city' => $data->pix_merchant_city,
                'pix_key'           => $data->pix_key,
                'whatsapp'          => Helpers::sanitizeString($data->whatsapp),
            ]);

        $this->assertDatabaseHas('partners', [
            'name'              => $data->name,
            'owner_name'        => $data->owner_name,
            'domain'            => $data->domain,
            'pix_key_type'      => $data->pix_key_type,
            'pix_merchant_name' => $data->pix_merchant_name,
            'pix_merchant_city' => $data->pix_merchant_city,
            'pix_key'           => $data->pix_key,
            'whatsapp'          => Helpers::sanitizeString($data->whatsapp),
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);

        foreach ($settings as $setting) {
            $this->assertDatabaseHas('settings', [
                'key'        => $setting->key,
                'value'      => $setting->value,
                'model_type' => Partner::class,
                'model_id'   => $partner->id,
            ]);
        }
    }

    /**
     * @test
     */
    public function canUpdateRemovingPaymentServiceToken()
    {
        $partner = Partner::factory()
            ->forUser()
            ->create();

        $data = Partner::factory()
            ->forUser()
            ->make();

        $keyToBeRemoved = Helpers::genRandomString();
        Setting::factory()->create([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
            'key'        => $keyToBeRemoved,
        ]);

        $settings = Setting::factory()->count(2)->create([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);

        $newSetting = Setting::factory()->make([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);
        $newKey     = $newSetting->key;

        $data->settings                  = Setting::convertBoolean($settings);
        $data->settings->$newKey         = $newSetting->value;
        $data->settings->$keyToBeRemoved = '';

        $userData = User::factory()->make();

        $data->email    = $userData->email;
        $data->document = $userData->document;
        $data->phone    = $userData->phone;

        Passport::actingAs($this->user);

        $response = $this->json('PUT', self::URL . "/$partner->id", $data->toArray());

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson([
                'name'              => $data->name,
                'owner_name'        => $data->owner_name,
                'domain'            => $data->domain,
                'pix_key_type'      => $data->pix_key_type,
                'pix_merchant_name' => $data->pix_merchant_name,
                'pix_merchant_city' => $data->pix_merchant_city,
                'pix_key'           => $data->pix_key,
                'whatsapp'          => Helpers::sanitizeString($data->whatsapp),
            ]);

        $this->assertDatabaseHas('partners', [
            'name'              => $data->name,
            'owner_name'        => $data->owner_name,
            'domain'            => $data->domain,
            'pix_key_type'      => $data->pix_key_type,
            'pix_merchant_name' => $data->pix_merchant_name,
            'pix_merchant_city' => $data->pix_merchant_city,
            'pix_key'           => $data->pix_key,
            'whatsapp'          => Helpers::sanitizeString($data->whatsapp),
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => $data->name,
            'email'    => $data->email,
            'phone'    => Helpers::sanitizeString($data->phone),
            'document' => Helpers::sanitizeString($data->document),
        ]);

        foreach ($settings as $setting) {
            $this->assertDatabaseHas('settings', [
                'key'        => $setting->key,
                'value'      => $setting->value,
                'model_type' => Partner::class,
                'model_id'   => $partner->id,
            ]);
        }

        $this->assertDatabaseHas('settings', [
            'key'        => $newSetting->key,
            'value'      => $newSetting->value,
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);

        $this->assertDatabaseMissing('settings', [
            'key'        => $keyToBeRemoved,
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);
    }

    /**
     * @test
     */
    public function canDelete()
    {
        $data = Partner::factory()
            ->forUser()
            ->create();

        Passport::actingAs($this->user);

        $response = $this->json('DELETE', self::URL . "/$data->id");

        $response->assertStatus(204)
            ->assertSee(null);

        $this->assertSoftDeleted('partners', [
            'id' => $data->id
        ]);
    }

    /**
     * @test
     */
    public function canRestore()
    {
        $data = Partner::factory()
            ->forUser()
            ->create();

        $data->delete();

        Passport::actingAs($this->user);

        $response = $this->json('PATCH', self::URL . "/$data->id/restore");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($data->makeHidden(['deleted_at', 'updated_at',])->toArray());

        $this->assertDatabaseHas('partners', [
            "id"   => $data->id,
            "name" => $data->name,
        ]);
    }

    /**
     * @test
     */
    public function canReturnProfile()
    {
        $user = User::factory()->create();
        $user->assignRole(UserRoles::PARTNER);

        $partner = Partner::factory()
            ->create([
                'user_id' => $user->id,
            ]);

        Passport::actingAs($user);

        $response = $this->json('GET', self::URL . "/me");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($partner->toArray());


        $user2 = User::factory()->create();
        $user2->assignRole(UserRoles::PARTNER);

        $partner2 = Partner::factory()
            ->create([
                'user_id' => $user2->id,
            ]);

        $response = $this->json('GET', self::URL . "/me");

        $response->assertStatus(200)
            ->assertJsonStructure(self::DATA_STRUCTURE)
            ->assertJson($partner->toArray());
    }

    /**
     * @test
     */
    public function canGetPartnerConfig()
    {
        $partnerUser = User::factory()->create();
        $partnerUser->assignRole(UserRoles::PARTNER);

        $partner = Partner::factory()
            ->create([
                'domain'  => Helpers::genRandomString(),
                'user_id' => $partnerUser->id,
            ]);

        $partnerSetting = Setting::factory()->create([
            'key'        => Helpers::genRandomString(),
            'value'      => Helpers::genRandomString(),
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);

        $adminSetting = Setting::factory()->create([
            'key'   => Helpers::genRandomString(),
            'value' => Helpers::genRandomString(),
        ]);

        $response = $this->json('GET', '/api/partners/config?domain=' . $partner->domain);

        $response->assertStatus(200)
            ->assertJson([
                'partner'  => [
                    'id'                => $partner->id,
                    'name'              => $partner->name,
                    'whatsapp'          => $partner->whatsapp,
                    'domain'            => $partner->domain,
                    'pix_key'           => $partner->pix_key,
                    'pix_key_type'      => $partner->pix_key_type,
                    'pix_merchant_name' => $partner->pix_merchant_name,
                    'pix_merchant_city' => $partner->pix_merchant_city,
                ],
                'settings' => [
                    $partnerSetting->key => $partnerSetting->value,
                ]
            ])
            ->assertJsonMissing([
                'settings' => [
                    $adminSetting->key => $adminSetting->value,
                ]
            ]);
    }

    /**
     * @test
     */
    public function canUpdateItself()
    {
        $user = User::factory()->create();
        $user->assignRole(UserRoles::PARTNER);
        $partner = Partner::factory()->create([
            'user_id' => $user->id,
        ]);

        $setting1 = Setting::factory()->create([
            'key'        => SettingEnum::whatsapp_link,
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);
        $setting2 = Setting::factory()->create([
            'model_type' => Partner::class,
            'model_id'   => $partner->id,
        ]);
        $value    = Helpers::genRandomString();

        Passport::actingAs($user);
        $response = $this->json('PUT', self::URL . "/update/me", [
            'settings' => [
                SettingEnum::whatsapp_link => $value,
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'settings' => [
                    SettingEnum::whatsapp_link => $value,
                    $setting2->key             => $setting2->value,
                ]
            ])
            ->assertJsonMissing([
                'settings' => [
                    SettingEnum::whatsapp_link => $setting1->value,
                ]
            ]);
    }
}
