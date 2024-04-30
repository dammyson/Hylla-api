<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'archived', 'favorite'
    ];
    
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
