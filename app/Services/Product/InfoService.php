<?php

namespace App\Services\Product;

use App\Services\BaseServiceInterface;
use App\Models\Product;
use App\Models\ProductFile;

class InfoService implements BaseServiceInterface
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function run()
    {
        $product = Product::with(['specifications', 'assets', 'posts'])->findorfail($this->id);
        $category = Product::select('category')->distinct()->where('product_id',$this->id )->get();
        $product['file_categories'] = $category ;
        return $product; 
    }
}