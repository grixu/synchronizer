<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Checksum
{
    protected ?string $checksum = null;

    public function __construct(protected Map $map, protected Model $model)
    {
    }

    protected function prepareChecksumData(): string
    {
        return json_encode(
            $this->model->only(
                $this->map->getModelFieldsArray()
            )
        );
    }

    public function validate(): bool
    {
        if (!$this->isChecksumEnabled()) {
            return false;
        }

        $checksumFieldName = $this->getChecksumFieldName();

        if (empty($this->model->$checksumFieldName)) {
            return false;
        }

        return Hash::check($this->prepareChecksumData(), $this->model->$checksumFieldName);
    }

    protected function isChecksumEnabled(): bool
    {
        return config('synchronizer.checksum.control') == true || !empty(config('synchronizer.checksum.control'));
    }

    protected function getChecksumFieldName(): string
    {
        $md5FieldName = config('synchronizer.checksum.field');
        if (empty($md5FieldName)) {
            throw new EmptyMd5FieldNameInConfigException();
        }

        return $md5FieldName;
    }

    public function update(): void
    {
        if (!$this->isChecksumEnabled()) {
            return;
        }

        $md5FieldName = $this->getChecksumFieldName();
        $this->model->$md5FieldName = $this->getChecksum();
        $this->model->save();
    }

    public function getChecksum(): string
    {
        if (empty($this->checksum)) {
            $this->checksum = Hash::make($this->prepareChecksumData());
        }

        return $this->checksum;
    }
}
