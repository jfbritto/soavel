<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Expense;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_lists_expenses()
    {
        Expense::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.expenses.index'));
        $response->assertStatus(200);
    }

    public function test_create_shows_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.expenses.create'));
        $response->assertStatus(200);
    }

    public function test_store_creates_expense()
    {
        $data = [
            'descricao' => 'Troca de óleo',
            'valor' => 250.00,
            'categoria' => 'manutencao',
            'data' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)->post(route('admin.expenses.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['descricao' => 'Troca de óleo', 'user_id' => $this->user->id]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('admin.expenses.store'), []);
        $response->assertSessionHasErrors(['descricao', 'valor', 'categoria', 'data']);
    }

    public function test_store_with_vehicle()
    {
        $vehicle = Vehicle::factory()->create();

        $data = [
            'descricao' => 'Polimento',
            'valor' => 300,
            'categoria' => 'limpeza',
            'data' => now()->format('Y-m-d'),
            'vehicle_id' => $vehicle->id,
        ];

        $response = $this->actingAs($this->user)->post(route('admin.expenses.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['vehicle_id' => $vehicle->id]);
    }

    public function test_update_modifies_expense()
    {
        $expense = Expense::factory()->create();

        $data = [
            'descricao' => 'Despesa atualizada',
            'valor' => 500,
            'categoria' => 'documentacao',
            'data' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)->put(route('admin.expenses.update', $expense), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'descricao' => 'Despesa atualizada']);
    }

    public function test_destroy_deletes_expense()
    {
        $expense = Expense::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.expenses.destroy', $expense));

        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
