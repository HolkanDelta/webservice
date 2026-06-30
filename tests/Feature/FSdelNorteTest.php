<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Service;
use App\Models\Client;
use Illuminate\Support\Facades\Http;

class FSdelNorteTest extends TestCase
{
    use RefreshDatabase;

    public function test_fsdelnorte_command_success()
    {
        // 1. Arrange: Create Service and Client
        $service = Service::create([
            'name' => 'MAPON_FSDELNORTE',
            'description' => 'FSdelNorte',
            'base_url' => 'https://gps.fsdelnorte.com/api/v1/webhooks/gps',
            'recurrence' => true,
        ]);

        $client = Client::create([
            'name' => 'Client Test FSdelNorte',
            'user_name' => 'test_user',
            'user_pass' => 'test_pass',
            'token' => 'fsdelnorte-bearer-token',
            'apikey' => 'mapon-api-key',
        ]);

        $service->clients()->attach($client->id);

        // 2. Mock HTTP calls (Mapon and FSdelNorte)
        Http::fake([
            // Mapon units list
            'acceso.holkan.com.mx/api/v1/unit/list.json?key=mapon-api-key' => Http::response([
                'status' => 'ok',
                'data' => [
                    'units' => [
                        ['unit_id' => 123]
                    ]
                ]
            ], 200),

            // Mapon unit details for unit 123
            'acceso.holkan.com.mx/api/v1/unit/list.json?key=mapon-api-key&unit_id=123&include%5B0%5D=device&include%5B1%5D=altitude' => Http::response([
                'status' => 'ok',
                'data' => [
                    'units' => [
                        [
                            'unit_id' => 123,
                            'lat' => 25.6866,
                            'lng' => -100.3161,
                            'speed' => 60,
                            'direction' => 180,
                            'last_update' => '2026-06-26 12:00:00', // parsed under America/Mexico_City
                            'label' => 'ECO-100',
                            'number' => 'PLATE-100',
                            'state' => [
                                'name' => 'driving'
                            ]
                        ]
                    ]
                ]
            ], 200),

            // FSdelNorte Webhook endpoint
            'gps.fsdelnorte.com/api/v1/webhooks/gps' => Http::response([
                'status' => 'success',
                'message' => '1 position accepted'
            ], 200)
        ]);

        // 3. Act: Run the Artisan command
        $this->artisan('app:fsdelnorte-command')
            ->assertExitCode(0);

        // 4. Assert: Check if FSdelNorte was called with correct payload and headers
        Http::assertSent(function ($request) use ($client) {
            if ($request->url() !== 'https://gps.fsdelnorte.com/api/v1/webhooks/gps') {
                return false;
            }

            // Verify bearer token header
            if ($request->header('Authorization')[0] !== 'Bearer ' . $client->token) {
                return false;
            }

            $payload = $request->data();
            if (count($payload) !== 1) {
                return false;
            }

            $item = $payload[0];
            // Last update parsed from America/Mexico_City ('2026-06-26 12:00:00') to UTC
            // On June 26, America/Mexico_City is UTC-6, so 12:00:00 becomes 18:00:00 UTC
            return $item['timestamp'] === '2026-06-26T18:00:00Z'
                && $item['economico'] === 'ECO-100'
                && $item['placa'] === 'PLATE-100'
                && $item['latitude'] === 25.6866
                && $item['longitude'] === -100.3161
                && $item['speed_kmh'] === 60
                && $item['course'] === 180
                && $item['engine_state'] === true
                && $item['event'] === 1;
        });

        // 5. Assert: Check ServiceLog was created in the DB
        $this->assertDatabaseHas('service_logs', [
            'service_id' => $service->id,
            'status' => 'success',
        ]);
    }
}
