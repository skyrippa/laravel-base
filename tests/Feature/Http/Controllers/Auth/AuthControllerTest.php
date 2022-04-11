<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Enums\UserRoles;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed');
    }

    private const URL = '/api/auth';

    /**
     * @test
     */
    public function non_authenticated_users_cannot_access_the_following_endpoints_api()
    {
        $index = $this->json('GET', self::URL . '/user');
        $index->assertStatus(401);

        $logout = $this->json('GET', self::URL . '/logout');
        $logout->assertStatus(401);
    }

    /**
     * @test
     */
    public function can_authenticate()
    {
        $password = 'password';
        $user     = User::factory()->create();
        $role     = UserRoles::SUPER_ADMIN;

        Artisan::call('db:seed');

        $user->assignRole($role);

        $structure = [
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'permissions' => [
                    '*' => []
                ]
            ]
        ];

        // with email
        $dataLogin = [
            'login'    => $user->email,
            'password' => $password
        ];

        $response = $this->post(self::URL . '/login', $dataLogin);

        $response->assertStatus(200)
            ->assertJsonStructure($structure)
            ->assertJson([
                'user' => [
                    'name'  => $user->name,
                    'role'  => $role,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * @test
     */
    public function can_get_current_user()
    {
        $user = User::factory()->create();

        Artisan::call('db:seed');

        $user->assignRole(UserRoles::SUPER_ADMIN);

        Passport::actingAs($user);

        $response = $this->get(self::URL . '/user');

        $structure = [
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'permissions' => [
                    '*' => []
                ]
            ]
        ];

        $response->assertStatus(200)
            ->assertJsonStructure($structure)
            ->assertJson([
                'user' => [
                    'name'  => $user->name,
                    'role'  => UserRoles::SUPER_ADMIN,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * @test
     */
    public function cannot_authenticate_with_wrong_data()
    {
        $password = 'secret';
        $user     = User::factory()->create();

        $role = UserRoles::SUPER_ADMIN;

        Artisan::call('db:seed');

        $user->assignRole($role);

        $user->assignRole(UserRoles::SUPER_ADMIN);

        $fakeUser = User::factory()->make();

        // wrong email and password
        $dataLogin = [
            'login'    => $fakeUser->email,
            'password' => $fakeUser->password,
        ];

        $response = $this->post(self::URL . '/login', $dataLogin);

        $response->assertStatus(401)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Usuário ou senha incorretos']]
                ]
            ]);

        // wrong password
        $dataLogin = [
            'login'    => $user->email,
            'password' => $fakeUser->password,
        ];
        $response  = $this->post(self::URL . '/login', $dataLogin);

        $response->assertStatus(401)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Usuário ou senha incorretos']]
                ]
            ]);

        // wrong email
        $dataLogin = [
            'login'    => $fakeUser->email,
            'password' => $password,
        ];
        $response  = $this->post(self::URL . '/login', $dataLogin);

        $response->assertStatus(401)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Usuário ou senha incorretos']]
                ]
            ]);

        // without data
        $dataLogin = [];
        $response  = $this->post(self::URL . '/login', $dataLogin);

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function can_logout()
    {
        $user = User::factory()->create();

        $response = $this->get(self::URL . '/logout');

        $response->assertStatus(401)
            ->assertJsonStructure(['message'])
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);


        Passport::actingAs($user);

        $response = $this->get(self::URL . '/logout');

        $response->assertStatus(200)
            ->assertJsonStructure(['message'])
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    /**
     * @test
     */
    public function canRecoverPassword()
    {
        $user = User::factory()->create();
        $user->assignRole(UserRoles::SUPER_ADMIN);

        $dataPassword = [
            'email' => $user->email,
        ];

        // EXPECTED BEHAVIOR
        $response = $this->post('/api/auth/recover_password', $dataPassword);

        $response->assertStatus(200);

        $this->assertDatabaseHas('password_resets', $dataPassword);

        // WITHOUT DATA
        $response = $this->post('/api/auth/recover_password', []);

        $response->assertStatus(404)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Not Found!'],]
                ]
            ]);


        // UNKNOWN EMAIL
        $user = User::factory()->make();

        $dataPassword = [
            'email' => $user->email,
        ];

        $response = $this->post('/api/auth/recover_password', $dataPassword);

        $response->assertStatus(404)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Not Found!'],]
                ]
            ]);
    }

    /**
     * @test
     */
    public function canResetPassword()
    {
        $password    = 'secret';
        $errorString = 'error string';

        $user = User::factory()->create();
        $user->assignRole(UserRoles::SUPER_ADMIN);

        Artisan::call('db:seed');

        $token = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        DB::table('password_resets')->insert([
            'email'      => $user->email,
            'token'      => bcrypt($token),
            'created_at' => Carbon::now()
        ]);

        // EXPECTED BEHAVIOR
        $dataPassword = [
            'token'                 => $token,
            'password'              => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->post('/api/auth/reset_password', $dataPassword);

        $structure = [
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'permissions' => [
                    '*' => []
                ]
            ]
        ];

        $response->assertStatus(200)
            ->assertJsonStructure($structure)
            ->assertJson([
                'user' => [
                    'name'  => $user->name,
                    'role'  => UserRoles::SUPER_ADMIN,
                    'email' => $user->email,
                ]
            ]);


        // WRONG TOKEN
        $dataPassword = [
            'token'                 => $errorString,
            'password'              => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->post('/api/auth/reset_password', $dataPassword);

        $response->assertStatus(404)
            ->assertJson([
                'error'   => true,
                'message' => [
                    ['error' => ['Código inválido']]
                ]
            ]);


        // UNMATCHING PASSWORDS
        $dataPassword = [
            'token'                 => $token,
            'password'              => $password,
            'password_confirmation' => $errorString,
        ];

        $response = $this->post('/api/auth/reset_password', $dataPassword);

        $response->assertStatus(422);


        // NO DATA
        $dataPassword = [];

        $response = $this->post('/api/auth/reset_password', $dataPassword);

        $response->assertStatus(422);
    }
}
