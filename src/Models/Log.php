<?php

namespace Grixu\Synchronizer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string model
 * @property int model_id
 * @method static create(array $array)
 */
class Log extends Model
{
    public $timestamps = true;

    public $table = 'synchronizer_logs';

    protected $casts = [
        'model_id' => 'integer',
        'log' => 'array',
    ];

    protected $fillable = [
        'model',
        'model_id',
        'log',
    ];
}
