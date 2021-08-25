<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Process\Abstracts\AbstractLoader;

class FakeLoader extends AbstractLoader
{
    public function buildQuery(?array $foreignKeys = []): static
    {
        $this->query = FakeForeignSqlSourceModel::query()
            ->select(
                'Knt_Nazwa1',
                'Knt_Nazwa2',
                'Knt_Kraj',
                'Knt_KodP',
                'Knt_Miasto',
                'Knt_Ulica',
                'Knt_Nip',
                'Knt_Powiat',
                'Knt_Wojewodztwo',
                'Knt_Telefon1',
                'Knt_Telefon2',
                'Knt_EMail',
                'Knt_LimitOkres',
                'Knt_AtrWlascicielFrsID',
                'Knt_GIDNumer',
                'Knt_SyncTimeStamp',
            )
            ->whereNotNull([
                               'Knt_Nazwa1',
                               'Knt_Kraj',
                               'Knt_KodP',
                               'Knt_Miasto',
                               'Knt_Ulica',
                               'Knt_Nip',
                               'Knt_Powiat',
                               'Knt_Wojewodztwo',
                               'Knt_Telefon1',
                               'Knt_Telefon2',
                               'Knt_EMail',
                               'Knt_LimitOkres',
                               'Knt_AtrWlascicielFrsID',
                               'Knt_GIDNumer',
                               'Knt_SyncTimeStamp',
                           ]);

        if (!empty($foreignKeys)) {
            $this->query = $this->query->whereIn('Knt_GIDNumer', $foreignKeys);
        }

        return $this;
    }
}
