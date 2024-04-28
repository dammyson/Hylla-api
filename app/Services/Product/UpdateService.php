<?php

namespace App\Services\Product;

use App\Services\BaseServiceInterface;


class UpdateService implements BaseServiceInterface
{
    protected $client;
    protected $data;

    public function __construct($client, $data)
    {
        $this->client = $client;
        $this->data = $data;
    }

    public function run()
    {
        return \DB::transaction(function () {
            $this->client->fill($this->data)->save();
            return $this->client->fresh();
        });
    }

}