<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = app(App\Http\Controllers\sdk\navigation::class)->login(73);
echo "LOGIN RESULT:\n" . $result . "\n";
$client = App\Models\Client::whereId(73)->first();
echo "SAVED TOKEN:\n" . $client->token . "\n";
