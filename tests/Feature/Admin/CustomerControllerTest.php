<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_lists_customers()
    {
        Customer::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.customers.index'));
        $response->assertStatus(200);
    }

    public function test_create_shows_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.customers.create'));
        $response->assertStatus(200);
    }

    public function test_store_creates_customer()
    {
        $data = [
            'nome' => 'João Silva',
            'cpf' => '123.456.789-00',
            'telefone' => '(28) 99999-0000',
            'email' => 'joao@teste.com',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['nome' => 'João Silva']);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), []);
        $response->assertSessionHasErrors(['nome', 'telefone']);
    }

    public function test_store_validates_unique_cpf()
    {
        Customer::factory()->create(['cpf' => '111.222.333-44']);

        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Teste',
            'cpf' => '111.222.333-44',
            'telefone' => '(28) 99999-0000',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    public function test_show_displays_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.customers.show', $customer));
        $response->assertStatus(200);
        $response->assertSee($customer->nome);
    }

    public function test_update_modifies_customer()
    {
        $customer = Customer::factory()->create();

        $data = [
            'nome' => 'Nome Atualizado',
            'telefone' => '(28) 99999-1111',
        ];

        $response = $this->actingAs($this->user)->put(route('admin.customers.update', $customer), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'nome' => 'Nome Atualizado']);
    }

    public function test_destroy_deletes_customer_without_sales()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.customers.destroy', $customer));

        $response->assertRedirect();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_destroy_fails_with_sales()
    {
        $customer = Customer::factory()->create();
        Sale::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->user)->delete(route('admin.customers.destroy', $customer));

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_cpf_check_returns_existing()
    {
        Customer::factory()->create(['cpf' => '123.456.789-00']);

        $response = $this->actingAs($this->user)->getJson(route('admin.customers.cpf-check', [
            'cpf' => '123.456.789-00',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'nome']);
    }

    public function test_cpf_check_returns_null_when_not_found()
    {
        $response = $this->actingAs($this->user)->getJson(route('admin.customers.cpf-check', [
            'cpf' => '999.999.999-99',
        ]));

        $response->assertStatus(200);
    }

    public function test_quick_store_creates_customer()
    {
        $response = $this->actingAs($this->user)->postJson(route('admin.customers.quick-store'), [
            'nome' => 'Quick Customer',
            'telefone' => '(28) 99999-5555',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', ['nome' => 'Quick Customer']);
    }
}
