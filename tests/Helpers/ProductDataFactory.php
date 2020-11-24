<?php

namespace Grixu\Synchronizer\Tests\Helpers;

/**
 * Class ProductDataFactory
 * @package Grixu\SyncLog\Tests\Helpers
 */
class ProductDataFactory extends Factory
{
    /**
     * @return ProductDataFactory
     */
    public static function new(): ProductDataFactory
    {
        return new self();
    }

    /**
     * @param array $parameters
     * @return ProductData
     */
    public function create(array $parameters = []): ProductData
    {
        return new ProductData(
            $parameters +
            [
                'name' => 'Test',
                'index' => '00000',
                'ean' => '000000',
                'weight' => 1.00,
                'price' => 100.50,
            ]
        );
    }
}
