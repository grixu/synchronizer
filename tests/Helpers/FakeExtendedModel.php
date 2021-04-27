<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Attributes\SynchronizeWith;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[SynchronizeWith(Product::class)]
class FakeExtendedModel extends Model
{
    protected $table = 'products';

    protected $guarded = ['id'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            Brand::class,
            'brand_id',
            'id'
        );
    }
}
