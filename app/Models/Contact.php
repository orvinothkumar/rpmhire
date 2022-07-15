<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contactId',
        'contactData',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
