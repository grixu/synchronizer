<?php

namespace Grixu\Synchronizer\Tests\Config;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\EngineConfig;
use Grixu\Synchronizer\Config\EngineConfigFactory;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Str;

class EngineConfigFactoryTest extends TestCase
{
    protected EngineConfigFactory $obj;

    /** @test */
    public function it_returns_sync_config_object()
    {
        $config = $this->makeObj();
        $this->assertEquals(EngineConfig::class, $config::class);
        $this->assertNotEmpty($config->getModel());
        $this->assertNotEmpty($config->getKey());
    }

    protected function makeObj(
        array|bool $timestamps = [],
        array $ids = [],
        array $fields = [],
        int $mode = EngineConfig::EXCLUDED,
        array|bool|null $checksum = null
    ): EngineConfigInterface {
        return EngineConfigFactory::make(
            model: FakeForeignSqlSourceModel::class,
            key: 'xlId',
            fields: $fields,
            mode: $mode,
            checksumField: $checksum,
            timestamps: $timestamps,
            ids: $ids
        );
    }

    protected function basicAssertions(EngineConfigInterface $config)
    {
        $this->assertEquals(EngineConfigInterface::class, $config::class);
        $this->assertNotEmpty($config->getModel());
        $this->assertNotEmpty($config->getKey());
    }

    /** @test */
    public function it_allows_to_define_checksum()
    {
        $config = $this->makeObj(checksum: 'checksum');
        $this->assertNotEmpty($config->getChecksumField());
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_allows_to_empty_checksum()
    {
        $config = $this->makeObj();
        $this->assertEmpty($config->getChecksumField());
    }

    protected function useDisabledChecksum($app)
    {
        $app->config->set('synchronizer.checksum.control', false);
    }

    /** @test */
    public function it_allows_to_disable_checksum()
    {
        $config = $this->makeObj(checksum: false);
        $this->assertEmpty($config->getChecksumField());
    }

    /** @test */
    public function it_allows_to_define_timestamps()
    {
        $timestamps = ['one'];
        $config = $this->makeObj(timestamps: $timestamps);

        $this->assertNotEmpty($config->getTimestamps());
        $this->assertEquals($timestamps, $config->getTimestamps());
    }

    /** @test */
    public function it_allows_to_disable_timestamps()
    {
        $config = $this->makeObj(timestamps: false);
        $this->assertEmpty($config->getTimestamps());
    }

    /** @test */
    public function it_set_up_default_timestamps_when_noting_defined()
    {
        $config = $this->makeObj();

        $this->assertNotEmpty($config->getTimestamps());
        $this->assertNotEquals(config('synchronizer.checksum.timestamps'), $config->getTimestamps());
        $this->assertEquals(
            array_map(fn ($item) => Str::camel($item), config('synchronizer.checksum.timestamps')),
            $config->getTimestamps()
        );
    }
}
