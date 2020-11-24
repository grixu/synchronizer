<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * @property string name
 * @property string index
 * @property string ean
 * @property double price
 * @package Domain\Product\Models
 */
class Product extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $casts = [
        'name' => 'string',
        'index' => 'string',
        'ean' => 'string',
        'weight' => 'double',
        'price' => 'double',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'index',
        'ean',
        'eshop',
    ];
}
