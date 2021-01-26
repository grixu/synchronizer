<?php

namespace Grixu\Synchronizer\Models;

use Grixu\Synchronizer\Factories\ExcludedFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string model
 * @property string model_id
 * @property bool update_empty
 * @property int id
 * @method static create(array $array)
 * @method static factory()
 */
class ExcludedField extends Model
{
    use HasFactory;

    public $table = 'synchronizer_excluded_fields';
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

    public static function newFactory()
    {
        return ExcludedFieldFactory::new();
    }
}
