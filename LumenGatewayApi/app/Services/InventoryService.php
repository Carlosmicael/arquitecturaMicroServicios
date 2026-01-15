<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class InventoryService
{
    use ConsumesExternalService;

    public $baseUri;
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.inventory.base_uri');
        $this->secret = config('services.inventory.secret');
        
        if (empty($this->baseUri)) {
            throw new \RuntimeException('INVENTORY_SERVICE_BASE_URL is not configured in .env file');
        }
    }

    public function obtainInventory()
    {
        return $this->performRequest('GET', '/inventory');
    }

    public function obtainInventoryByBook($book_id)
    {
        return $this->performRequest('GET', "/inventory/book/{$book_id}");
    }

    public function getAvailableQuantity($book_id)
    {
        return $this->performRequest('GET', "/inventory/available/{$book_id}");
    }

    public function createInventory($data)
    {
        return $this->performRequest('POST', '/inventory', $data);
    }

    public function updateInventory($id, $data)
    {
        return $this->performRequest('PUT', "/inventory/{$id}", $data);
    }

    public function deleteInventory($id)
    {
        return $this->performRequest('DELETE', "/inventory/{$id}");
    }

    public function reserveUnits($data)
    {
        return $this->performRequest('POST', '/inventory/reserve', $data);
    }

    public function releaseUnits($data)
    {
        return $this->performRequest('POST', '/inventory/release', $data);
    }
}