<?php

namespace Grixu\Synchronizer\Tests\Config;

use Exception;
use Grixu\Synchronizer\Config\Exceptions\InterfaceNotImplemented;
use Grixu\Synchronizer\Config\ProcessConfig;
use Grixu\Synchronizer\Process\Handlers\DefaultErrorHandler;
use Grixu\Synchronizer\Process\Handlers\DefaultSyncHandler;
use Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class ProcessConfigTest extends TestCase
{
    /** @test */
    public function it_make_process_config_object_with_default_values()
    {
        $config = $this->makeObj();
        $this->assertEquals(ProcessConfig::class, $config::class);
        $this->assertNotEmpty($config->getErrorHandler());
        $this->assertNotEmpty($config->getSyncHandler());
        $this->checkJobs($config);
    }

    protected function makeObj(
        string|null $sync = null,
        string|null $error = null,
        string $jobs = 'default'
    ): ProcessConfig {
        if (empty($sync)) {
            $sync = DefaultSyncHandler::class;
        }

        if (empty($error)) {
            $error = DefaultErrorHandler::class;
        }

        return ProcessConfig::make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            jobsConfig: $jobs,
            syncHandler: $sync,
            errorHandler: $error,
        );
    }

    protected function checkJobs(ProcessConfig $config)
    {
        $this->assertNotEmpty($config->getCurrentJob());
        $this->assertNotEmpty($config->getNextJob());
    }

    /** @test */
    public function it_could_take_job_config_string()
    {
        $config = $this->makeObj(jobs: 'load-all-and-parse');
        $this->checkJobs($config);
    }

    /**
     * @test
     * @environment-setup useEmptyJobConfig
     */
    public function it_throws_exception_if_there_is_no_default_job_config()
    {
        try {
            $this->makeObj();
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * @environment-setup useEmptyHandlersConfig
     */
    public function it_throws_exception_if_there_is_no_default_handlers_config()
    {
        try {
            $this->makeObj();
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_checking_interfaces_implemented_by_loader_and_parser()
    {
        try {
            $this->makeObj(
                sync: FakeSyncHandler::class,
                error: FakeErrorHandler::class
            );

            $this->assertTrue(false);
        } catch (InterfaceNotImplemented) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_throws_exception_when_current_job_is_lower_than_zero()
    {
        $config = $this->makeObj();

        try {
            $config->setCurrentJob(-1);
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_throws_exception_when_current_job_is_greater_than_jobs_count()
    {
        $config = $this->makeObj();

        try {
            $config->setCurrentJob(count(config('synchronizer.jobs.default')) + 1);
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_check_sync_handler_on_setter()
    {
        $config = $this->makeObj();

        try {
            $config->setSyncHandler(FakeSyncHandler::class);
            $this->fail();
        } catch (InterfaceNotImplemented) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_error_handler_on_setter()
    {
        $config = $this->makeObj();

        try {
            $config->setErrorHandler(FakeErrorHandler::class);
            $this->fail();
        } catch (InterfaceNotImplemented) {
            $this->assertTrue(true);
        }
    }

    protected function useEmptyJobConfig($app)
    {
        $app->config->set('synchronizer.jobs', [
            'default' => [],
        ]);
    }

    protected function useEmptyHandlersConfig($app)
    {
        $app->config->set('synchronizer.handlers', []);
    }
}
