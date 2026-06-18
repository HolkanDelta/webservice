<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Service;

class ClientServiceSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            'MAPON_LANDSTAR' => [
                'Holkan Mapon-Landstar 1',
            ],
            'MAPON_CONTROL_T' => [
                'Transportes Ruiz',
                'JOSE JORGE HUITZIL SANTIAGO', 
                'MONICA CORONA LINARES', 
                'Ariana Rodriguez Reyes PRO',
            ],
            'MAPON - RECURSO_CONFIABLE' => [
                'Transportes Pichardo',
                'MONICA CORONA LINARES',
                'LOGISTICA Y MANIOBRAS CAVA',
                'Hector Manuel Orozco',
                'Ernesto Soto Molina - Recurso Confiable WALMART',
                'Ernesto Soto Molina',
                'Ramiro Enrique Vargas Romero',
                'TMV', 
                'Doble cero', 
                'Conrado Martinez Tehuitzil',
                'PALLUS CARGO', 
                'TRAVILSA', 
                'Transportes Terrestres Vazquez',
                'Filiberto Villaseñor Villaseñor',
                'JOSE JORGE HUITZIL SANTIAGO',
                'Luis Guillermo Becerril Palma',
                'Logística tres Guerreras',
            ],
            'MAPON - KRONH' => [
                'Grupo Preuss - KRONH - HLK',
                'PICHARDO - KRONH',
            ],
            'MAPON - PEGASUS' => [
                'Zahid Hiram Donis Vargas',
                'Jose Rigoberto Carreto',
                'Oscar Raful Luciano',
                'Salgado Gonzalez Luis',
                'Jael Olmedo Hernandez',
                'Jorge Fernando Sanjuan Asomoza (Cemex)',
            ],
            'MAPON - SKYANGEL' => [
                'Magdalena Martínez Garduño',
                'TRANSMARS',
                'Transportes Olvera - SKY',
                'Transportes España',
            ],
            'MAPON_FLEET_ROCKET' => [
                'David Murillo Muñoz',
            ],
        ];

        foreach ($mappings as $serviceName => $clientNames) {
            $service = Service::where('name', $serviceName)->first();
            if (!$service) {
                continue;
            }

            foreach ($clientNames as $name) {
                // Try exact match first
                $client = Client::where('name', $name)->first();

                // If not found, try fuzzy match
                if (!$client) {
                    $cleanName = preg_replace('/(\s+CT|\s+PRO)$/', '', $name);
                    $client = Client::where('name', 'LIKE', "%{$cleanName}%")->first();
                }

                if ($client) {
                    // Check if already attached to avoid duplication
                    if (!$service->clients()->where('client_id', $client->id)->exists()) {
                        $service->clients()->attach($client->id);
                    }
                }
            }
        }
    }
}
