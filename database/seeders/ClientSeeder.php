<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clients')->insert([
            'name' => 'MONICA CORONA LINARES',
            'user_name' => 'ws_avl_holkan',
            'user_pass' => 'bGcU#584znHl#0',
            'company_id' => '41013',
            'token' => '',
            'apiKey' => '1ab76f78522558b2b173bc9fc7407fe0d2beb6d5',
        ]);
        DB::table('clients')->insert([
            'name' => 'TMV - RC',
            'user_name' => 'ws_avl_holkan',
            'user_pass' => 'bGcU#584znHl#0',
            'company_id' => '0',
            'token' => '',
            'apiKey' => '3e4dc0869548fdf2a04c78044a956eb1693421ee',
        ]);
        DB::table('clients')->insert([
            'name' => 'Ernesto Soto Molina',
            'user_name' => 'wm_10501_HOLKAN',
            'user_pass' => 'TDzG#767xFEC*3',
            'company_id' => '10501',
            'token' => '',
            'apiKey' => '0e734070ddd773d644504637a8fc08df36a24a72',
        ]);
        DB::table('clients')->insert([
            'name' => 'Transportes Ruiz',
            'user_name' => 'holkanmx',
            'user_pass' => 'K98$5Gbn7R$',
            'company_id' => '',
            'token' => '',
            'apiKey' => '52d333ed5ba8c266e3a00bcec1f5f268b435baa9',
        ]);
        DB::table('clients')->insert([
            'name' => 'Hector Manuel Orozco',
            'user_name' => '71kWB3Hl3j2XbHAmRzi1Dxf.z8PytRK41hu864PS',
            'user_pass' => '$2y$10$R8F0Zev4ZwpgfxePQCE0q.UWE8dXg.8ps',
            'company_id' => '',
            'token' => '',
            'apiKey' => 'b3b0dfdb62e76ae4fce99e278a322cc8cbfebd2d',
        ]);
        
    }
}
