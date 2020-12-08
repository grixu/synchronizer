<?php


namespace Grixu\Synchronizer\Tests;


use Grixu\Synchronizer\Events\SynchronizerDetectChangesEvent;
use Grixu\Synchronizer\Exceptions\EmptyMd5FieldInModelException;
use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Grixu\Synchronizer\Models\SynchronizerField;
use Grixu\Synchronizer\Models\SynchronizerLog;
use Grixu\Synchronizer\Synchronizer;
use Grixu\Synchronizer\SynchronizerLogger;
use Grixu\Synchronizer\SynchronizerMap;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Grixu\Synchronizer\Tests\Helpers\Product;
use Grixu\Synchronizer\Tests\Helpers\ProductData;
use Grixu\Synchronizer\Tests\Helpers\ProductDataFactory;
use Grixu\Synchronizer\Tests\Helpers\ProductFactory;
use Grixu\Synchronizer\Tests\Helpers\SynchronizerFieldFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class SynchronizerTest
 * @package Grixu\Synchronizer\Tests
 */
class SynchronizerTest extends BaseTestCase
{
    protected array $map;
    protected DataTransferObject $foreign;
    protected Model $local;

    protected function setUp(): void
    {
        parent::setUp();

        $this->map = [
            'name' => 'name',
            'index' => 'index',
            'ean' => 'ean',
            'weight' => 'weight',
        ];

        $this->local = ProductFactory::new()->make();
        $this->local->id = 1;
        $this->foreign = ProductDataFactory::new()->create();
    }

    protected function withoutMd5Control($app)
    {
        $app->config->set('synchronizer.md5_control', false);
    }

    protected function withoutMd5FieldName($app)
    {
        $app->config->set('synchronizer.md5_control', true);
        $app->config->set('synchronizer.md5_local_model_field', null);
    }

    protected function startsChecks($obj)
    {
        $this->assertNotEquals($this->local->ean, $this->foreign->ean);
        $this->assertNotEquals($this->local->index, $this->foreign->index);
        $this->assertNotEquals($this->local->weight, $this->foreign->weight);
        $this->assertNotEquals($obj->getMd5(), $this->local->checksum);
    }

    protected function endChecks($obj)
    {
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
        $this->assertEquals($obj->getMd5(), $this->local->checksum);
    }

    protected function clearLogsAndCheck()
    {
        SynchronizerLog::query()->delete();
        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    protected function clearAndCreateExcludedField(int $update=1)
    {
        SynchronizerField::query()->delete();
        SynchronizerFieldFactory::new()->create(
            [
                'model' => get_class($this->local),
                'field' => 'name',
                'update_empty' => $update
            ]
        );
    }

    protected function checkMd5($obj)
    {
        $this->assertEquals(SynchronizerMap::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getMap()->getToMd5()));
        $this->assertNotEmpty($obj->getMap()->getToMd5());
        $this->assertNotEmpty($obj->getForeign());
    }

    /** @test */
    public function check_constructor()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEquals(SynchronizerMap::class, get_class($obj->getMap()));
        $this->assertEquals(Product::class, get_class($obj->getLocal()));
        $this->assertEquals(ProductData::class, get_class($obj->getForeign()));
        $this->assertEquals(SynchronizerLogger::class, get_class($obj->getLogger()));
    }

    /** @test */
    public function check_sync_without_excluded_nulls()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        $obj->sync(false);

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->endChecks($obj);
    }

    /** @test */
    public function check_sync_with_excluded_nulls()
    {
        $this->clearAndCreateExcludedField();

        $this->local->name = null;
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEmpty($this->local->name);
        $this->assertNotEmpty($this->foreign->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->endChecks($obj);
    }

    /** @test */
    public function check_sync_with_excluded_nulls_but_nothing_on_it()
    {
        $this->clearAndCreateExcludedField(0);
        $this->local->name = 'check';
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertNotEquals($this->foreign->name, $this->local->name);
        $this->endChecks($obj);
    }

    /** @test */
    public function check_sync_with_empty_excluded_field()
    {
        $this->clearAndCreateExcludedField(0);
        $this->local->name = null;
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEmpty($this->local->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertNotEmpty($this->foreign->name);
        $this->assertEmpty($this->local->name);
        $this->endChecks($obj);
    }

    /** @test */
    public function check_changes()
    {
        $this->clearAndCreateExcludedField();
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);
        $this->checkMd5($obj);

        $returned = $obj->checkChanges();
        $this->assertTrue($returned);
    }

    /**
     * @environment-setup withoutMd5Control
     * @test
     */
    public function check_changes_when_turnoff_md5_check()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);
        $this->checkMd5($obj);

        $returned = $obj->checkChanges();
        $this->assertTrue($returned);
    }

    /**
     * @environment-setup withoutMd5FieldName
     * @test
     */
    public function check_changes_when_no_checksum_field_name_configured()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);
        $this->checkMd5($obj);

        try {
            $obj->checkChanges();
            $this->assertTrue(false);
        } catch (EmptyMd5FieldNameInConfigException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function check_changes_when_checksum_field_is_empty()
    {
        $this->local->checksum = null;
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);
        $this->checkMd5($obj);

        $returned = $obj->checkChanges();
        $this->assertTrue($returned);
    }

    /** @test */
    public function get_md5()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEmpty($obj->getMd5());

        $obj->checkChanges();
        $this->assertNotEmpty($obj->getMd5());
    }

    /** @test */
    public function check_with_nothing_changed_in_foreign()
    {
        $this->local->name = $this->foreign->name;
        $this->local->ean = $this->foreign->ean;
        $this->local->index = $this->foreign->index;
        $this->local->weight = $this->foreign->weight;

        $obj = new Synchronizer($this->map, $this->local, $this->foreign);
        $this->local->checksum = md5(
            json_encode(
                collect($this->foreign->toArray())
                    ->only($obj->getMap()->getToMd5()->values())
                    ->toArray()
            )
        );

        $this->assertEquals($this->local->name, $this->foreign->name);
        $this->assertEquals($this->local->ean, $this->foreign->ean);
        $this->assertEquals($this->local->index, $this->foreign->index);
        $this->assertEquals($this->local->weight, $this->foreign->weight);

        $obj->checkChanges();
        $this->assertEquals($obj->getMd5(), $this->local->checksum);

        $obj->sync();

        $this->assertEquals($this->local->name, $this->foreign->name);
        $this->endChecks($obj);
    }

    /** @test */
    public function check_event_is_fired()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        Event::fake();

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->endChecks($obj);

        Event::assertDispatched(SynchronizerDetectChangesEvent::class);
    }

    /**
     * @environment-setup withoutMd5Control
     * @test
     */
    public function check_event_is_no_fired()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->startsChecks($obj);
        $this->clearLogsAndCheck();

        Event::fake();

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
        $this->assertEmpty($obj->getMd5());

        Event::assertNotDispatched(SynchronizerDetectChangesEvent::class);
    }
}
