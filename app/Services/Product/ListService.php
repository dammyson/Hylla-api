<?php

namespace App\Services\Product;

use App\Services\BaseServiceInterface;
use App\Models\Product;

class ListService implements BaseServiceInterface
{
    protected $company_id;

    public function __construct($company_id=null)
    {
        $this->company_id = $company_id;
    }

    public function run()
    {
        if ($this->company_id) {
            return Product::where('company_id', $this->company_id)->latest()->get();
        }
        return [];
    }
}