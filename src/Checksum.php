<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Illuminate\Database\Eloquent\Model;

class Checksum
{
    protected ?string $md5 = null;

    public function __construct(protected Map $map, protected Model $model)
    {
    }

    protected function calculate(): void
    {
        $this->md5 = md5(
            json_encode(
                $this->model->only(
                    $this->map->getModelFieldsArray()
                )
            )
        );
    }

    public function validate(): bool
    {
        if (!$this->isChecksumEnabled()) {
            return false;
        }

        $md5FieldName = $this->getMd5FieldName();

        if (empty($this->model->$md5FieldName)) {
            return false;
        }

        return $this->model->$md5FieldName === $this->getMd5();
    }

    protected function isChecksumEnabled(): bool
    {
       return config('synchronizer.checksum.control') == true || !empty(config('synchronizer.checksum.control'));
    }

    protected function getMd5FieldName(): string
    {
        $md5FieldName = config('synchronizer.checksum.field');
        if (empty($md5FieldName)) {
            throw new EmptyMd5FieldNameInConfigException();
        }

        return $md5FieldName;
    }

    public function update(): void
    {
        if (!$this->isChecksumEnabled())
            return;

        $md5FieldName = $this->getMd5FieldName();
        $this->model->$md5FieldName = $this->getMd5();
        $this->model->save();
    }

    public function getMd5(): string
    {
        if (empty($this->md5)) {
            $this->calculate();
        }

        return $this->md5;
    }
}
