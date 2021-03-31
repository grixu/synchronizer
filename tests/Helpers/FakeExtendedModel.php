<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Attributes\SynchronizeWith;

#[SynchronizeWith(Product::class)]
class FakeExtendedModel extends Product
{
    protected $table = 'products';
}
