<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class ProductData
 * @package Grixu\SyncLog\Tests\Helpers
 */
class ProductData extends DataTransferObject
{
    public ?int $id;

    public string $name;

    public string $index;

    public string $ean;

    public float $weight;

    public bool $eshop=false;

    public ?float $price;
}
