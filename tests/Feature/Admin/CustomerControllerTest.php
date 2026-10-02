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
        $this->assertDatabaseHas('customers', ['nome' => 'Quick Customer', 'tipo_pessoa' => 'pf']);
    }

    // ── Pessoa jurídica ───────────────────────────────────────────────────────

    public function test_store_without_tipo_pessoa_defaults_to_pf()
    {
        $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Maria Souza',
            'cpf' => '222.333.444-55',
            'telefone' => '(28) 99999-0000',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', ['nome' => 'Maria Souza', 'tipo_pessoa' => 'pf', 'cpf' => '222.333.444-55', 'cnpj' => null]);
    }

    public function test_store_creates_pj_customer_with_cnpj()
    {
        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Transportes Silva Ltda',
            'tipo_pessoa' => 'pj',
            'cnpj' => '12.345.678/0001-90',
            'telefone' => '(28) 3333-0000',
            'email' => 'contato@silva.com.br',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'nome' => 'Transportes Silva Ltda',
            'tipo_pessoa' => 'pj',
            'cnpj' => '12.345.678/0001-90',
            'cpf' => null,
        ]);
    }

    public function test_store_pj_discards_cpf_and_pf_discards_cnpj()
    {
        $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Empresa X', 'tipo_pessoa' => 'pj',
            'cnpj' => '11.111.111/0001-11', 'cpf' => '999.999.999-99',
            'telefone' => '(28) 3333-0000',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['nome' => 'Empresa X', 'cnpj' => '11.111.111/0001-11', 'cpf' => null]);

        $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Pessoa Y', 'tipo_pessoa' => 'pf',
            'cpf' => '888.888.888-88', 'cnpj' => '22.222.222/0001-22',
            'telefone' => '(28) 3333-0000',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['nome' => 'Pessoa Y', 'cpf' => '888.888.888-88', 'cnpj' => null]);
    }

    public function test_store_validates_unique_cnpj()
    {
        Customer::factory()->pj()->create(['cnpj' => '12.345.678/0001-90']);

        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Outra Empresa',
            'tipo_pessoa' => 'pj',
            'cnpj' => '12.345.678/0001-90',
            'telefone' => '(28) 3333-0000',
        ]);

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_store_rejects_invalid_tipo_pessoa()
    {
        $response = $this->actingAs($this->user)->post(route('admin.customers.store'), [
            'nome' => 'Teste', 'tipo_pessoa' => 'xx', 'telefone' => '(28) 3333-0000',
        ]);

        $response->assertSessionHasErrors('tipo_pessoa');
    }

    public function test_update_can_switch_pf_to_pj_and_clears_cpf()
    {
        $customer = Customer::factory()->create(['cpf' => '123.456.789-00']);

        $this->actingAs($this->user)->put(route('admin.customers.update', $customer), [
            'nome' => 'Agora Empresa Ltda',
            'tipo_pessoa' => 'pj',
            'cnpj' => '33.333.333/0001-33',
            'telefone' => '(28) 3333-0000',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'tipo_pessoa' => 'pj', 'cnpj' => '33.333.333/0001-33', 'cpf' => null]);
    }

    public function test_update_keeps_own_cnpj_without_unique_conflict()
    {
        $customer = Customer::factory()->pj()->create(['cnpj' => '44.444.444/0001-44']);

        $this->actingAs($this->user)->put(route('admin.customers.update', $customer), [
            'nome' => $customer->nome, 'tipo_pessoa' => 'pj',
            'cnpj' => '44.444.444/0001-44', 'telefone' => '(28) 3333-0000',
        ])->assertSessionHasNoErrors();
    }

    public function test_index_searches_by_cnpj_and_shows_pj_badge()
    {
        Customer::factory()->pj()->create(['nome' => 'Auto Peças Central', 'cnpj' => '55.555.555/0001-55']);
        Customer::factory()->create(['nome' => 'Fulano Qualquer']);

        $response = $this->actingAs($this->user)->get(route('admin.customers.index', ['search' => '55.555.555']));
        $response->assertStatus(200);
        $response->assertSee('Auto Peças Central');
        $response->assertSee('55.555.555/0001-55');
        $response->assertDontSee('Fulano Qualquer');
        $response->assertSee('title="Pessoa Jurídica"', false);
    }

    public function test_show_displays_cnpj_for_pj()
    {
        $customer = Customer::factory()->pj()->create(['cnpj' => '66.666.666/0001-66']);

        $response = $this->actingAs($this->user)->get(route('admin.customers.show', $customer));
        $response->assertStatus(200);
        $response->assertSee('CNPJ');
        $response->assertSee('66.666.666/0001-66');
        $response->assertSee('Pessoa Jurídica');
    }

    public function test_edit_form_preselects_pj()
    {
        $customer = Customer::factory()->pj()->create();

        $response = $this->actingAs($this->user)->get(route('admin.customers.edit', $customer));
        $response->assertStatus(200);
        $response->assertSee('value="pj" class="custom-control-input tipo-pessoa"', false);
        $response->assertSee('value="pj" class="custom-control-input tipo-pessoa"
                    checked', false);
    }

    public function test_cpf_check_finds_customer_by_cnpj()
    {
        $customer = Customer::factory()->pj()->create(['cnpj' => '77.777.777/0001-77']);

        $response = $this->actingAs($this->user)->getJson(route('admin.customers.cpf-check', [
            'cnpj' => '77.777.777/0001-77',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['id' => $customer->id, 'tipo_pessoa' => 'pj', 'documento' => '77.777.777/0001-77', 'documento_label' => 'CNPJ']);
    }

    public function test_quick_store_creates_pj_customer()
    {
        $response = $this->actingAs($this->user)->postJson(route('admin.customers.quick-store'), [
            'nome' => 'Quick Empresa Ltda',
            'tipo_pessoa' => 'pj',
            'cnpj' => '88.888.888/0001-88',
            'cpf' => '111.111.111-11',
            'telefone' => '(28) 3333-5555',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['tipo_pessoa' => 'pj', 'documento' => '88.888.888/0001-88', 'documento_label' => 'CNPJ']);
        $this->assertDatabaseHas('customers', ['nome' => 'Quick Empresa Ltda', 'tipo_pessoa' => 'pj', 'cnpj' => '88.888.888/0001-88', 'cpf' => null]);
    }

    public function test_quick_store_rejects_duplicate_cpf_and_cnpj_with_422()
    {
        Customer::factory()->create(['cpf' => '123.456.789-00']);
        Customer::factory()->pj()->create(['cnpj' => '99.999.999/0001-99']);

        $this->actingAs($this->user)->postJson(route('admin.customers.quick-store'), [
            'nome' => 'Dup PF', 'cpf' => '123.456.789-00', 'telefone' => '(28) 3333-5555',
        ])->assertStatus(422)->assertJsonValidationErrors('cpf');

        $this->actingAs($this->user)->postJson(route('admin.customers.quick-store'), [
            'nome' => 'Dup PJ', 'tipo_pessoa' => 'pj', 'cnpj' => '99.999.999/0001-99', 'telefone' => '(28) 3333-5555',
        ])->assertStatus(422)->assertJsonValidationErrors('cnpj');
    }

    public function test_sale_create_lists_pj_customer_with_cnpj()
    {
        Customer::factory()->pj()->create(['nome' => 'Frota Express Ltda', 'cnpj' => '10.101.010/0001-10']);

        $response = $this->actingAs($this->user)->get(route('admin.sales.create'));
        $response->assertStatus(200);
        $response->assertSee('Frota Express Ltda — 10.101.010/0001-10');
        $response->assertSee('id="mc_cnpj"', false);
    }
}
