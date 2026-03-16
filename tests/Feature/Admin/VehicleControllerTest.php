<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeature;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_requires_auth()
    {
        $this->get(route('admin.vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_vehicles()
    {
        Vehicle::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.vehicles.index'));
        $response->assertStatus(200);
    }

    public function test_index_filters_by_status()
    {
        Vehicle::factory()->disponivel()->create();
        Vehicle::factory()->vendido()->create();

        $response = $this->actingAs($this->user)->get(route('admin.vehicles.index', ['status' => 'disponivel']));
        $response->assertStatus(200);
    }

    public function test_create_shows_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.vehicles.create'));
        $response->assertStatus(200);
    }

    public function test_store_creates_vehicle()
    {
        $data = [
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'versao' => 'EXL',
            'ano_fabricacao' => 2023,
            'ano_modelo' => 2024,
            'km' => 15000,
            'preco' => 120000,
            'cor' => 'Branco',
            'combustivel' => 'flex',
            'transmissao' => 'automatico',
            'portas' => 4,
            'categoria' => 'sedan',
            'status' => 'disponivel',
            'features' => ['Ar condicionado', 'Direção elétrica'],
        ];

        $response = $this->actingAs($this->user)->post(route('admin.vehicles.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicles', ['marca' => 'Honda', 'modelo' => 'Civic']);
        $this->assertEquals(2, VehicleFeature::count());
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('admin.vehicles.store'), []);
        $response->assertSessionHasErrors(['marca', 'modelo', 'ano_fabricacao', 'ano_modelo', 'km', 'preco', 'cor', 'combustivel', 'transmissao', 'portas', 'categoria', 'status']);
    }

    public function test_store_validates_enum_values()
    {
        $data = [
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'ano_fabricacao' => 2023,
            'ano_modelo' => 2024,
            'km' => 0,
            'preco' => 100000,
            'cor' => 'Branco',
            'combustivel' => 'invalido',
            'transmissao' => 'invalido',
            'portas' => 4,
            'categoria' => 'invalido',
            'status' => 'invalido',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.vehicles.store'), $data);
        $response->assertSessionHasErrors(['combustivel', 'transmissao', 'categoria', 'status']);
    }

    public function test_show_displays_vehicle()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.vehicles.show', $vehicle));
        $response->assertStatus(200);
        $response->assertSee($vehicle->marca);
    }

    public function test_edit_shows_form()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.vehicles.edit', $vehicle));
        $response->assertStatus(200);
    }

    public function test_update_modifies_vehicle()
    {
        $vehicle = Vehicle::factory()->create();

        $data = [
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'ano_fabricacao' => 2024,
            'ano_modelo' => 2025,
            'km' => 0,
            'preco' => 150000,
            'cor' => 'Prata',
            'combustivel' => 'flex',
            'transmissao' => 'automatico',
            'portas' => 4,
            'categoria' => 'sedan',
            'status' => 'disponivel',
        ];

        $response = $this->actingAs($this->user)->put(route('admin.vehicles.update', $vehicle), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'marca' => 'Toyota', 'preco' => 150000]);
    }

    public function test_destroy_deletes_vehicle_without_sales()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.vehicles.destroy', $vehicle));

        $response->assertRedirect();
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_destroy_fails_with_sales()
    {
        $vehicle = Vehicle::factory()->create();
        Sale::factory()->create(['vehicle_id' => $vehicle->id]);

        $response = $this->actingAs($this->user)->delete(route('admin.vehicles.destroy', $vehicle));

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_toggle_destaque()
    {
        $vehicle = Vehicle::factory()->create(['destaque' => false]);

        $response = $this->actingAs($this->user)->patch(route('admin.vehicles.toggleDestaque', $vehicle));

        $response->assertJson(['destaque' => true]);
        $this->assertTrue($vehicle->fresh()->destaque);
    }

    public function test_generates_unique_slug_on_store()
    {
        $data = [
            'marca' => 'Fiat',
            'modelo' => 'Argo',
            'ano_fabricacao' => 2024,
            'ano_modelo' => 2024,
            'km' => 0,
            'preco' => 75000,
            'cor' => 'Vermelho',
            'combustivel' => 'flex',
            'transmissao' => 'manual',
            'portas' => 4,
            'categoria' => 'hatch',
            'status' => 'disponivel',
        ];

        $this->actingAs($this->user)->post(route('admin.vehicles.store'), $data);
        $this->actingAs($this->user)->post(route('admin.vehicles.store'), $data);

        $slugs = Vehicle::pluck('slug');
        $this->assertCount(2, $slugs);
        $this->assertNotEquals($slugs[0], $slugs[1]);
    }
}
