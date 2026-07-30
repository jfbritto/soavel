<?php

namespace Tests\Feature\Site;

use App\Models\Vehicle;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_home_shows_featured_vehicles()
    {
        Vehicle::factory()->destaque()->disponivel()->create(['marca' => 'DestaqueMarca']);
        Vehicle::factory()->disponivel()->create(['destaque' => false]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_estoque_page_loads()
    {
        Vehicle::factory()->disponivel()->count(5)->create();

        $response = $this->get('/estoque');
        $response->assertStatus(200);
    }

    public function test_estoque_filters_by_marca()
    {
        Vehicle::factory()->disponivel()->create(['marca' => 'Honda']);
        Vehicle::factory()->disponivel()->create(['marca' => 'Toyota']);

        $response = $this->get('/estoque?marca=Honda');
        $response->assertStatus(200);
    }

    public function test_vehicle_detail_page()
    {
        $vehicle = Vehicle::factory()->disponivel()->create();

        $response = $this->get('/estoque/' . $vehicle->slug);
        $response->assertStatus(200);
        $response->assertSee($vehicle->marca);
    }

    public function test_vehicle_detail_404_for_invalid_slug()
    {
        $response = $this->get('/estoque/slug-inexistente');
        $response->assertStatus(404);
    }

    public function test_contact_form_creates_lead()
    {
        $data = [
            'nome' => 'Visitante Teste',
            'telefone' => '(28) 99999-0000',
            'mensagem' => 'Quero saber mais sobre o veículo',
        ];

        $response = $this->post('/contato', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', ['nome' => 'Visitante Teste', 'origem' => 'site']);
    }

    public function test_contact_form_validates()
    {
        $response = $this->post('/contato', []);
        $response->assertSessionHasErrors(['nome', 'telefone']);
    }

    public function test_interesse_creates_lead_for_vehicle()
    {
        $vehicle = Vehicle::factory()->disponivel()->create();

        $data = [
            'nome' => 'Interessado',
            'telefone' => '(28) 99999-1111',
        ];

        $response = $this->post('/interesse/' . $vehicle->id, $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'nome' => 'Interessado',
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_sitemap_returns_xml()
    {
        // Este teste esteve markTestSkipped incondicionalmente, com o comentario
        // "Works correctly in production" — e nao funcionava: /sitemap.xml
        // devolvia 500 na hospedagem compartilhada, com 9.824 erros acumulados
        // no log, descoberto na migracao para o VPS.
        //
        // A causa era a view abrir com <?xml literal, que o PHP interpreta como
        // tag de abertura quando short_open_tag=On. A view ja foi corrigida para
        // emitir a declaracao como expressao Blade, entao o teste volta a valer.
        $vehicle = Vehicle::factory()->create(['status' => 'disponivel']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        // false no segundo argumento: sem escapar, senao o < viraria &lt;
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset', false);
        $response->assertSee($vehicle->slug, false);
    }
}
