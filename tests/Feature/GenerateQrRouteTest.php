<?php

namespace Tests\Feature;

use Tests\TestCase;

class GenerateQrRouteTest extends TestCase
{
    public function test_geneqr_index_route_loads()
    {
        $response = $this->get('/geneqr');

        $response->assertStatus(200);
    }

    public function test_generate_qr_action_renders_view()
    {
        $response = $this->post('/qrcode/generate', ['id_qrcode' => [1]]);

        $response->assertStatus(200);
    }
}
