<?php

namespace Grixu\Synchronizer\Logs\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $model
 * @property string $batch_id
 * @property int $total_changes
 * @property array $log
 * @method static create(array $array)
 */
class Log extends Model
{
    public $timestamps = true;

    public $table = 'synchronizer_logs';

    protected $casts = [
        'log' => 'array',
        'type' => 'integer',
        'changed' => 'integer',
    ];

    protected $fillable = [
        'model',
        'batch_id',
        'changed',
        'type',
        'log',
    ];
}
