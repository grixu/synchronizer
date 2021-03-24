<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\DataTransferObjects\CustomerData;
use Grixu\Synchronizer\Abstracts\AbstractParser;
use Illuminate\Database\Eloquent\Model;

class FakeParser extends AbstractParser
{
    public function parseElement(Model $model): CustomerData
    {
        return new CustomerData(
            [
                'name' => $model->knt_nazwa1 . ' ' . $model->knt_nazwa2,
                'country' => $model->knt_kraj,
                'postalCode' => $model->knt_kodp,
                'city' => $model->knt_miasto,
                'street' => $model->knt_ulica,
                'vatNumber' => $model->knt_nip,
                'district' => $model->knt_powiat,
                'voivodeship' => $model->knt_wojewodztwo,
                'phone1' => $model->knt_telefon1,
                'phone2' => $model->knt_telefon2,
                'email' => $model->knt_email,
                'paymentPeriod' => (int) $model->knt_limitokres,
                'xlId' => (int) $model->knt_gidnumer,
                'syncTs' => now()->subYear(),
                'updatedAt' => now(),
            ]
        );
    }
}
