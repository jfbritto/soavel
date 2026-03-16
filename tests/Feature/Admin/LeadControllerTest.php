<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_lists_leads()
    {
        Lead::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.leads.index'));
        $response->assertStatus(200);
    }

    public function test_index_filters_by_status()
    {
        Lead::factory()->create(['status' => 'novo']);
        Lead::factory()->convertido()->create();

        $response = $this->actingAs($this->user)->get(route('admin.leads.index', ['status' => 'novo']));
        $response->assertStatus(200);
    }

    public function test_create_shows_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.leads.create'));
        $response->assertStatus(200);
    }

    public function test_store_creates_lead()
    {
        $data = [
            'nome' => 'Lead Teste',
            'telefone' => '(28) 99999-0000',
            'email' => 'lead@teste.com',
            'origem' => 'site',
            'status' => 'novo',
            'interesse' => 'Interesse em Civic',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.leads.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', ['nome' => 'Lead Teste']);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('admin.leads.store'), []);
        $response->assertSessionHasErrors(['nome', 'telefone', 'origem', 'status']);
    }

    public function test_update_modifies_lead()
    {
        $lead = Lead::factory()->create();

        $data = [
            'nome' => 'Lead Atualizado',
            'telefone' => '(28) 99999-1111',
            'origem' => 'whatsapp',
            'status' => 'em_contato',
        ];

        $response = $this->actingAs($this->user)->put(route('admin.leads.update', $lead), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'nome' => 'Lead Atualizado', 'status' => 'em_contato']);
    }

    public function test_destroy_deletes_lead()
    {
        $lead = Lead::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.leads.destroy', $lead));

        $response->assertRedirect();
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_update_status()
    {
        $lead = Lead::factory()->create(['status' => 'novo']);

        $response = $this->actingAs($this->user)->patch(route('admin.leads.status', $lead), [
            'status' => 'convertido',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'convertido']);
    }

    public function test_assign_to_user()
    {
        $lead = Lead::factory()->create(['user_id' => null]);
        $assignee = User::factory()->create();

        $response = $this->actingAs($this->user)->patch(route('admin.leads.assign', $lead), [
            'user_id' => $assignee->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'user_id' => $assignee->id]);
    }
}
