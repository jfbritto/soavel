<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class PartnerControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_lists_partners()
    {
        Partner::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('admin.partners.index'));
        $response->assertStatus(200);
    }

    public function test_store_creates_partner()
    {
        $data = [
            'nome' => 'Parceiro Teste',
            'cpf' => '123.456.789-00',
            'telefone' => '(28) 99999-0000',
            'email' => 'parceiro@teste.com',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.partners.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('partners', ['nome' => 'Parceiro Teste']);
    }

    public function test_update_modifies_partner()
    {
        $partner = Partner::factory()->create();

        $response = $this->actingAs($this->user)->put(route('admin.partners.update', $partner), [
            'nome' => 'Nome Atualizado',
            'telefone' => '(28) 99999-1111',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'nome' => 'Nome Atualizado']);
    }

    public function test_destroy_deletes_partner()
    {
        $partner = Partner::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.partners.destroy', $partner));

        $response->assertRedirect();
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_attach_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($this->user)->post(route('admin.vehicles.partners.attach', $vehicle), [
            'partner_id' => $partner->id,
            'percentual' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicle_partners', [
            'vehicle_id' => $vehicle->id,
            'partner_id' => $partner->id,
            'percentual' => 30,
        ]);
    }

    public function test_detach_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $partner = Partner::factory()->create();
        $vehicle->partners()->attach($partner->id, ['percentual' => 50]);

        $response = $this->actingAs($this->user)->delete(route('admin.vehicles.partners.detach', [$vehicle, $partner]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('vehicle_partners', [
            'vehicle_id' => $vehicle->id,
            'partner_id' => $partner->id,
        ]);
    }
}
