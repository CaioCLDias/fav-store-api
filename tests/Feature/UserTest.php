<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test admin can list all users.
     */
    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->count(5)->create();

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'is_admin']
                ]
            ]);

        // Deve incluir o admin + 5 usuários criados
        $this->assertCount(6, $response->json('data'));
    }


    /**
     * Test admin can create new user.
     */
    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $userData = [
            'name' => 'Novo Usuário',
            'email' => 'novo@aiqfome.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123',
            'is_admin' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Novo Usuário',
                    'email' => 'novo@aiqfome.com',
                    'is_admin' => false,
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'novo@aiqfome.com',
            'is_admin' => false,
        ]);
    }

    /**
     * Test admin can create admin user.
     */
    public function test_admin_can_create_admin_user(): void
    {
        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $userData = [
            'name' => 'Novo Admin',
            'email' => 'admin@aiqfome.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123',
            'is_admin' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Novo Admin',
                    'email' => 'admin@aiqfome.com',
                    'is_admin' => true,
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@aiqfome.com',
            'is_admin' => true,
        ]);
    }

    /**
     * Test regular user cannot create other users.
     */
    public function test_regular_user_cannot_create_other_users(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $userData = [
            'name' => 'Tentativa',
            'email' => 'tentativa@aiqfome.com',
            'password' => 'MinhaSenh@123',
            'is_admin' => false,
            'password_confirmation' => 'MinhaSenh@123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(403);
    }

    /**
     * Test user can view their own profile.
     */
    public function test_user_can_view_their_own_profile(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * Test user cannot view other user's profile.
     */
    public function test_user_cannot_view_other_users_profile(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    /**
     * Test admin can view any user's profile.
     */
    public function test_admin_can_view_any_users_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * Test user can update their own profile.
     */
    public function test_user_can_update_their_own_profile(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $updateData = [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@aiqfome.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Nome Atualizado',
                    'email' => 'atualizado@aiqfome.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@aiqfome.com',
        ]);
    }

    /**
     * Test regular user cannot change is_admin field.
     */
    public function test_regular_user_cannot_change_is_admin_field(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $updateData = [
            'name' => 'Nome Atualizado',
            'is_admin' => true, // Tentativa de se promover
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_admin']);
    }

    /**
     * Test user can delete their own account.
     */
    public function test_user_can_delete_their_own_account(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test admin can delete any user.
     */
    public function test_admin_can_delete_any_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test admin cannot delete themselves.
     */
    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    /**
     * Test admin can view trashed users.
     */
    public function test_admin_can_view_trashed_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $user->delete(); // Soft delete

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/trashed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'email', 'is_admin']
                ]
            ]);
    }

    /**
     * Test regular user cannot view trashed users.
     */
    public function test_regular_user_cannot_view_trashed_users(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/trashed');

        $response->assertStatus(403);
    }

    /**
     * Test admin can restore deleted user.
     */
    public function test_admin_can_restore_deleted_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $user->delete(); // Soft delete

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/users/{$user->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário restaurado com sucesso'
            ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }
}
