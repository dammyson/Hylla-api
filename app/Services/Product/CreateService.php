<?php

namespace App\Services\Product;

use App\Models\Image;
use App\Services\BaseServiceInterface;
use App\Models\Product;
use App\Models\Store;

class CreateService implements BaseServiceInterface
{
    protected $data, $code;

    public function __construct($data, $code)
    {
       
        $this->data = $data;
        $this->code = $code;
    }

    public function run()
    {
        return \DB::transaction(function () {
            $productData = $this->data;
            $product = Product::create([
                'code' => $this->code,
                'barcode_number' => $productData->barcode_number,
                'barcode_formats' => $productData->barcode_formats,
                'mpn' => $productData->mpn,
                'model' => $productData->model,
                'asin' => $productData->asin,
                'title' => $productData->title,
                'category' => $productData->category,
                'manufacturer' => $productData->manufacturer,
                'brand' => $productData->brand,
                'ingredients' => $productData->ingredients,
                'nutrition_facts' => $productData->nutrition_facts,
                'size' => $productData->size,
                'description' => $productData->description,
                'last_update' => $productData->last_update
            ]);


             // Create related Images
        foreach ($productData->images as $imageUrl) {
            Image::create([
                'product_id' => $product->id,
                'url' => $imageUrl
            ]);
        }

        // Create related Stores
        foreach ($productData->stores as $storeData) {
            Store::create([
                'product_id' => $product->id,
                'name' => $storeData->name,
                'country' => $storeData->country,
                'currency' => $storeData->currency,
                'currency_symbol' => $storeData->currency_symbol,
                'price' => $storeData->price,
                'sale_price' => $storeData->sale_price,
                'link' => $storeData->link,
                'availability' => $storeData->availability,
                'condition' => $storeData->condition,
                'last_update' => $storeData->last_update
            ]);
        }
            return $product;
        });
    }


    public function createSpecification($product_id)
    {
            

            return true;
    }
}