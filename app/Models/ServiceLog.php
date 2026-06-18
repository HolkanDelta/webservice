<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'status',
        'message',
        'runtime_ms',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
