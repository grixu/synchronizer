<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\DataTransferObjects\CustomerData;
use Grixu\Synchronizer\Process\Abstracts\AbstractParser;

class FakeParser extends AbstractParser
{
    public function parseElement($model): CustomerData
    {
        return CustomerData::from(
            [
                'name' => $model->Knt_Nazwa1 . ' ' . $model->Knt_Nazwa2,
                'country' => $model->Knt_Kraj,
                'postalCode' => $model->Knt_KodP,
                'city' => $model->Knt_Miasto,
                'street' => $model->Knt_Ulica,
                'vatNumber' => $model->Knt_Nip,
                'district' => $model->Knt_Powiat,
                'voivodeship' => $model->Knt_Wojewodztwo,
                'phone1' => $model->Knt_Telefon1,
                'phone2' => $model->Knt_Telefon2,
                'email' => $model->Knt_EMail,
                'paymentPeriod' => $model->Knt_LimitOkres,
                'xlId' => $model->Knt_GIDNumer,
                'syncTs' => $model->Knt_SyncTimeStamp,
                'updatedAt' => now(),
            ]
        );
    }
}
