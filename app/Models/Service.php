<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'base_url',
        'recurrence'
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }

    public function logs()
    {
        return $this->hasMany(ServiceLog::class);
    }

    public function logRun(string $status, ?string $message = null, int $runtimeMs = 0, ?array $payload = null): ServiceLog
    {
        return $this->logs()->create([
            'status' => $status,
            'message' => $message,
            'runtime_ms' => $runtimeMs,
            'payload' => $payload,
        ]);
    }
}
