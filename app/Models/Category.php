<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    public static function ensureCategories(array $categoryNames)
    {
        $categoryIds = [];
        foreach ($categoryNames as $categoryName) {
            $categoryName = trim($categoryName);
            if (!empty($categoryName)) {
                // Check if the category already exists
                $category = static::where('name', $categoryName)->first();

                // If it does not exist, create it
                if (!$category) {
                    $category = static::create(['name' => $categoryName]);
                }

                // Add the ID to the list
                $categoryIds[] = $category->id;
            }
        }
        return $categoryIds;
    }

}
