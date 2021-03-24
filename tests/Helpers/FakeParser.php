<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\DataTransferObjects\CustomerData;
use Grixu\Synchronizer\Contracts\ParserInterface;
use Illuminate\Database\Eloquent\Model;

class FakeParser implements ParserInterface
{
    public function parse(Model $fakeModel): CustomerData
    {
        ray($fakeModel);
        return new CustomerData(
            [
                'name' => $fakeModel->knt_nazwa1 . ' ' . $fakeModel->knt_nazwa2,
                'country' => $fakeModel->knt_kraj,
                'postalCode' => $fakeModel->knt_kodp,
                'city' => $fakeModel->knt_miasto,
                'street' => $fakeModel->knt_ulica,
                'vatNumber' => $fakeModel->knt_nip,
                'district' => $fakeModel->knt_powiat,
                'voivodeship' => $fakeModel->knt_wojewodztwo,
                'phone1' => $fakeModel->knt_telefon1,
                'phone2' => $fakeModel->knt_telefon2,
                'email' => $fakeModel->knt_email,
                'paymentPeriod' => (int) $fakeModel->knt_limitokres,
                'xlId' => (int) $fakeModel->knt_gidnumer,
                'syncTs' => now()->subYear(),
                'updatedAt' => now(),
            ]
        );
    }
}
