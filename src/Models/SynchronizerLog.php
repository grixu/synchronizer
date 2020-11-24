<?php

namespace Grixu\Synchronizer\Models;

use Grixu\Synchronizer\DataTransferObjects\SynchronizerLogData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class SynchronizerLog
 * @property string model
 * @property int model_id
 * @property SynchronizerLogData log
 * @package Grixu\Synchronizer\Models
 * @method static create(array $array)
 */
class SynchronizerLog extends Model
{
    use Notifiable;

    public $timestamps = true;

    public $table = 'synchronizer_logs';

    protected $casts = [
        'model_id' => 'integer',
        'log' => SynchronizerLogData::class,
    ];

    protected $fillable = [
        'model',
        'model_id',
        'log',
    ];
}
