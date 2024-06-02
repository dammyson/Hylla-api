<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCache extends Model
{
    protected $fillable = ['code', 'details'];
    use HasFactory;
}
