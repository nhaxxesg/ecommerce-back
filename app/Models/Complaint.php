<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'consumer_name',
        'consumer_email',
        'consumer_phone',
        'product_description',
        'complaint_detail',
        'status',
        'response_deadline',
    ];

    protected $casts = [
        'response_deadline' => 'datetime',
    ];
} 