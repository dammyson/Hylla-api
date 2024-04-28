<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'country', 'currency', 'currency_symbol', 'price', 
        'sale_price', 'link', 'availability', 'condition', 'last_update'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
