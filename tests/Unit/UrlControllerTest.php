<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Url;

class UrlControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_urls()
    {
        // Crear algunas URLs en la base de datos
        Url::factory()->create(['url' => 'https://www.example.com', 'url_key' => 'abc123']);
        Url::factory()->create(['url' => 'https://www.google.com', 'url_key' => 'xyz789']);

        // Hacer la solicitud GET al endpoint
        $response = $this->get('/api/url');

        // Afirmar que la respuesta es correcta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'url', 'url_key', 'short_url'],
                ],
                'current_page',
                'last_page',
                'total',
            ]);
    }

    /** @test */
    public function it_can_store_a_url()
    {
        // Hacer una solicitud POST para crear una URL
        $response = $this->post('/api/url', [
            'url' => 'https://www.example.com',
        ]);

        // Afirmar que se creó correctamente
        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'url', 'url_key', 'short_url']]);

        // Verificar que la URL se guardó en la base de datos
        $this->assertDatabaseHas('urls', ['url' => 'https://www.example.com']);
    }

    /** @test */
    public function it_fails_when_storing_an_invalid_url()
    {
        // Hacer una solicitud POST con una URL no válida
        $response = $this->post('/api/url', [
            'url' => 'invalid-url',
        ]);

        // Afirmar que se recibe un error de validación
        $response->assertStatus(400)
            ->assertJson(['status' => false, 'errors' => ['The url field must be a valid URL.']]);
    }


    /** @test */
    public function it_can_show_a_url()
    {
        $url = Url::factory()->create(['url' => 'https://www.example.com', 'url_key' => 'abc123']);

        $response = $this->get('/api/url/' . $url->url_key);

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $url->id, 'url' => $url->url, 'url_key' => $url->url_key]]);
    }

    /** @test */
    public function it_returns_404_when_url_key_not_found()
    {
        $response = $this->get('/api/url/nonexistent-key');

        $response->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'URL not found']);
    }


    /** @test */
    public function it_can_delete_a_url()
    {
        $url = Url::factory()->create(); // Crear una URL en la base de datos

        // Hacer la solicitud DELETE a la API
        $response = $this->delete('/api/url/' . $url->id);

        // Afirmar que la respuesta es 200 OK
        $response->assertStatus(200)
            ->assertJson(['id' => $url->id]);

        $this->assertSoftDeleted($url); 
    }
}
