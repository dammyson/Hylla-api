<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code','barcode_number', 'barcode_formats', 'mpn', 'model', 'asin', 'title',
        'category', 'manufacturer', 'brand', 'age_group', 'ingredients',
        'nutrition_facts', 'energy_efficiency_class', 'color', 'gender',
        'material', 'pattern', 'format', 'multipack', 'size', 'length', 'width',
        'height', 'weight', 'release_date', 'description', 'last_update'
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
