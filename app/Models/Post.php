<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'client_id',
        'content',
        'media',
        'scheduleDate',
        'platform',
        'postType',
        'status',
        'feedback',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
} 