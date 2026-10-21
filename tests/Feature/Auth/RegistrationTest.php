<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O cadastro público foi fechado: qualquer usuário entra no painel da loja.
 * O primeiro usuário nasce no provisionamento (AdminUserSeeder).
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_does_not_exist()
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_cannot_register_by_post()
    {
        $response = $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'Senha-forte-123',
            'password_confirmation' => 'Senha-forte-123',
        ]);

        $this->assertContains($response->status(), [404, 405]);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    public function test_login_page_has_no_register_link()
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('/register');
    }
}
