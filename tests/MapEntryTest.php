<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\MapEntry;
use Grixu\Synchronizer\Models\ExcludedField;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;

class MapEntryTest extends TestCase
{
    /** @test */
    public function not_excluded_fields()
    {
        $obj = new MapEntry('name', 'name');

        $this->assertTrue($obj->isSyncable());
        $this->assertFalse($obj->isTimestamp());
        $this->assertGetters($obj);
    }

    protected function assertGetters($obj, $field = 'name')
    {
        $this->assertEquals($field, $obj->getDtoField());
        $this->assertEquals($field, $obj->getModelField());
    }

    /** @test */
    public function excluded_field()
    {
        $excludedField = ExcludedField::factory()->create(
            [
                'field' => 'name',
                'model' => Model::class
            ]
        );

        $obj = new MapEntry('name', 'name', $excludedField);

        $this->assertFalse($obj->isSyncable());
        $this->assertFalse($obj->isTimestamp());
        $this->assertGetters($obj);
    }

    /** @test */
    public function excluded_field_with_null_update()
    {
        $excludedField = ExcludedField::factory()->create(
            [
                'field' => 'name',
                'model' => Model::class,
                'update_empty' => true
            ]
        );

        $obj = new MapEntry('name', 'name', $excludedField);

        $this->assertTrue($obj->isSyncable(''));
        $this->assertFalse($obj->isTimestamp());
        $this->assertGetters($obj);
    }

    /**
     * @test
     * @environment-setup timestampConfig
     */
    public function field_is_timestamp()
    {
        $obj = new MapEntry('updatedAt', 'updatedAt');

        $this->assertTrue($obj->isSyncable());
        $this->assertTrue($obj->isTimestamp());
        $this->assertGetters($obj, 'updatedAt');
    }
}
