<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration with valid data.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@aiqfome.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'is_admin'],
                    'token',
                    'token_type',
                    'expires_in'
                ]
            ]);


        $this->assertDatabaseHas('users', [
            'email' => 'joao@aiqfome.com',
            'is_admin' => false,
        ]);


        $user = User::where('email', 'joao@aiqfome.com')->first();
        $this->assertFalse($user->is_admin);
    }

    /**
     * Test registration fails with invalid data.
     */
    public function test_registration_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/auth/register', $invalidData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // Criar usuário existente
        User::factory()->create(['email' => 'joao@aiqfome.com']);

        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@aiqfome.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test user can login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->withCredentials('joao@aiqfome.com', 'MinhaSenh@123')->create();

        $loginData = [
            'email' => 'joao@aiqfome.com',
            'password' => 'MinhaSenh@123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'is_admin'],
                    'token',
                    'token_type',
                    'expires_in'
                ]
            ]);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->withCredentials('joao@aiqfome.com', 'MinhaSenh@123')->create();

        $loginData = [
            'email' => 'joao@aiqfome.com',
            'password' => 'SenhaErrada',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ]);
    }

    /**
     * Test login fails with soft deleted user.
     */
    public function test_login_fails_with_soft_deleted_user(): void
    {
        $user = User::factory()->withCredentials('joao@aiqfome.com', 'MinhaSenh@123')->create();
        $user->delete();

        $loginData = [
            'email' => 'joao@aiqfome.com',
            'password' => 'MinhaSenh@123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Conta desativada. Entre em contato com o suporte.'
            ]);
    }

    /**
     * Test user can logout successfully.
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ]);
    }

    /**
     * Test user can refresh token.
     */
    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'expires_in'
                ]
            ]);
    }

    /**
     * Test user can get own profile.
     */
    public function test_user_can_get_own_profile(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Dados do usuário obtidos com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ]);
    }

    /**
     * Test unauthenticated requests are rejected.
     */
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test admin user has correct JWT claims.
     */
    public function test_admin_user_has_correct_jwt_claims(): void
    {
        $admin = User::factory()->admin()->create();

        $loginData = [
            'email' => $admin->email,
            'password' => 'password',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200);

        $token = $response->json('data.token');
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertTrue($payload->get('is_admin'));
    }

    /**
     * Test regular user has correct JWT claims.
     */
    public function test_regular_user_has_correct_jwt_claims(): void
    {
        $user = User::factory()->regular()->create();

        $loginData = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200);

        $token = $response->json('data.token');
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertFalse($payload->get('is_admin'));
    }
}
