<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string Knt_Nazwa1
 * @property string Knt_Nazwa2
 * @property string Knt_Kraj
 * @property string Knt_KodP
 * @property string Knt_Miasto
 * @property string Knt_Ulica
 * @property string Knt_Nip
 * @property string Knt_Powiat
 * @property string Knt_Wojewodztwo
 * @property string Knt_Telefon1
 * @property string Knt_Telefon2
 * @property string Knt_EMail
 * @property int Knt_LimitOkres
 * @property int Knt_AtrWlascicielFrsID
 * @property int Knt_GIDNumer
 * @property Carbon Knt_SyncTimeStamp
 * @property int Knt_OpeNumer
 */
class FakeForeignSqlSourceModel extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = true;
    protected $table = 'KntKarty';
    protected $primaryKey = 'Knt_GIDNumer';
    protected $connection = 'xl';

    protected $casts = [
        'Knt_Nazwa1' => 'string',
        'Knt_Nazwa2' => 'string',
        'Knt_Kraj' => 'string',
        'Knt_KodP' => 'string',
        'Knt_Miasto' => 'string',
        'Knt_Ulica' => 'string',
        'Knt_Nip' => 'string',
        'Knt_Powiat' => 'string',
        'Knt_Wojewodztwo' => 'string',
        'Knt_Telefon1' => 'string',
        'Knt_Telefon2' => 'string',
        'Knt_EMail' => 'string',
        'Knt_LimitOkres' => 'integer',
        'Knt_OpeNumer' => 'integer',
        'Knt_AtrWlascicielFrsID' => 'integer',
        'Knt_GIDNumer' => 'integer',
    ];

    protected $dates = [
        'Knt_SyncTimeStamp',
    ];

    public static function newFactory(): FakeForeignSqlSourceFactory
    {
        return FakeForeignSqlSourceFactory::new();
    }
}

