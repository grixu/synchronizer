<?php

namespace Grixu\Synchronizer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransformerField
 * @property string model
 * @property string model_id
 * @property bool update_empty
 * @property int id
 * @package Grixu\Transformer
 * @method static create(array $array)
 */
class SynchronizerField extends Model
{
    public $table = 'synchronizer_fields';
    public $timestamps = true;

    protected $casts = [
        'field' => 'string',
        'model' => 'string',
        'update_empty' => 'boolean',
    ];

    protected $fillable = [
        'field',
        'model',
        'update_empty',
    ];
}
