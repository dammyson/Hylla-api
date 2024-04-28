<?php

namespace App\Services\Product;

use App\Services\BaseServiceInterface;
use App\Models\Product;

class SearchService implements BaseServiceInterface
{
    protected $data;

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    public function run()
    {
    
        // if ($this->data->has('name')) {
        //     dd('kkkkkkk');
        //     return Product::where('name', 'like', '%'.$this->data['name'].'%')->get();
        // }
        if ($this->data->has('category')) {
            return Product::where('category', 'like', '%'.$this->data['category'].'%')->get();
        }

        return [];
       // return Product::where('name', 'like', '%'.$this->data['name'].'%')->get();
    }
}