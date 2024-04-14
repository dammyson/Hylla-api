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
    
    protected $guarded = [];

    public function category(){
        return $this->belongsTo(Category::class);
    }


    
}
