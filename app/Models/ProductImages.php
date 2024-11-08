<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImages extends Model
{
    use HasFactory;

    protected $fillable = ['barcode', 'image_path'];

    protected $casts = [
        'image_path' => 'array',
    ];
}
