<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// LOGIN
test('login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'user' => ['id', 'email', 'nombre', 'tipo', 'es_tutor'],
            'token'
        ])
        ->assertJson(['status' => 'success']);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['status' => 'error']);
});

test('login fails without email', function () {
    $response = $this->post('/api/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

// LOGOUT
test('logout successfully', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
});

test('logout fails without authentication', function () {
    $response = $this->post('/api/logout');

    $response->assertStatus(401);
});

// AUTH
test('get authenticated user', function () {
    $user = User::factory()->create(['tipo' => 'alumno']);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/auth');

    $response->assertStatus(200)
        ->assertJson(['status' => 'success'])
        ->assertJsonStructure(['user' => ['id', 'email', 'es_tutor']]);
});

test('auth fails without token', function () {
    $response = $this->get('/api/auth');

    $response->assertStatus(401);
});

// GET USERS
test('get users list', function () {
    User::factory(3)->create(['tipo' => 'alumno']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'data' => [
                    '*' => ['id', 'email', 'nombre', 'tipo']
                ]
            ]
        ]);
});

test('filter users by tipo', function () {
    User::factory(2)->create(['tipo' => 'alumno']);
    User::factory(2)->create(['tipo' => 'tutor']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/users?tipo=alumno');

    $users = $response['data']['data'];
    foreach ($users as $u) {
        expect($u['tipo'])->toBe('alumno');
    }
});

test('search users by name', function () {
    User::factory()->create(['nombre' => 'Juan Perez']);
    User::factory()->create(['nombre' => 'Maria Garcia']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/users?search=Juan');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.data');
});

// CREATE USER
test('create user successfully', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/user/create', [
            'nombre' => 'Carlos',
            'apellidos' => 'García',
            'email' => 'carlos@example.com',
            'password' => 'password123',
            'tipo' => 'alumno',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['usuario' => ['id', 'email', 'nombre']]);

    $this->assertDatabaseHas('users', [
        'email' => 'carlos@example.com',
        'nombre' => 'Carlos',
    ]);
});

test('create fails with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/user/create', [
            'nombre' => 'Nuevo',
            'apellidos' => 'Lopez',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'tipo' => 'alumno',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('create fails with short password', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/user/create', [
            'nombre' => 'Carlos',
            'apellidos' => 'Perez',
            'email' => 'test@example.com',
            'password' => '12345',
            'tipo' => 'alumno',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

// UPDATE USER
test('update user successfully', function () {
    $user = User::factory()->create(['tipo' => 'alumno']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->put("/api/users/{$user->id}", [
            'nombre' => 'Nombre Nuevo',
            'email' => 'newemail@example.com',
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Usuario actualizado correctamente']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'nombre' => 'Nombre Nuevo',
        'email' => 'newemail@example.com',
    ]);
});

test('update fails with duplicate email', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->put("/api/users/{$user1->id}", [
            'email' => 'user2@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('update fails with non-existent user', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->put('/api/users/99999', [
            'nombre' => 'Test',
        ]);

    $response->assertStatus(404);
});

// CHANGE PASSWORD
test('change password successfully', function () {
    $user = User::factory()->create(['password' => Hash::make('oldpass123')]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/change-password', [
            'current_password' => 'oldpass123',
            'new_password' => 'newpass123',
            'new_password_confirmation' => 'newpass123',
        ]);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
});

test('change password fails with wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/change-password', [
            'current_password' => 'wrongpass',
            'new_password' => 'newpass123',
            'new_password_confirmation' => 'newpass123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('current_password');
});

test('change password fails with mismatched confirmation', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newpass123',
            'new_password_confirmation' => 'differentpass',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('new_password');
});

// DELETE USER
test('delete user successfully', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Usuario eliminado correctamente']);

    $this->assertNull(User::find($user->id));
});

test('delete fails with non-existent user', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete('/api/users/99999');

    $response->assertStatus(404)
        ->assertJson(['error' => 'Usuario no encontrado']);
});
