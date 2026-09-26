<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'telefono' => '+56 9 1234 5678',
            'ciudad' => 'Puerto Montt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('panel', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'telefono_whatsapp' => '+56912345678', 'comuna' => 'Puerto Montt']);
    }
}
