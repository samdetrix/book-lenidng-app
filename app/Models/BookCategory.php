<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    use HasFactory;
    protected $guards = [];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
