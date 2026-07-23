<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\SynchronizerServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncTestCase extends \Orchestra\Testbench\TestCase
{
    protected ProcessConfigInterface $processConfig;
    protected EngineConfigInterface $engineConfig;

    protected function getPackageProviders($app)
    {
        return [
            SynchronizerServiceProvider::class,
            \Spatie\LaravelRay\RayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('socius-models.checksum_field', 'checksum');

        // The foreign ERP source (KntKarty) was a committed test.sqlite fixture that no
        // longer exists; run it in-memory and seed it in setUp instead.
        $app['config']->set('database.connections.xl', [
            'driver' => 'sqlite',
            'url' => null,
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_customers_table.stub';
        try {
            (new \CreateCustomersTable())->up();
        } catch (\Throwable) {
        }

        $this->artisan('migrate')->run();

        // Bus::batch() needs job_batches; create it directly instead of publishing the
        // queue:batches-table migration, which accumulated duplicate stubs in the
        // testbench skeleton and re-ran on every migrate ("table already exists").
        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        $this->seedForeignSource();

        $this->processConfig = FakeProcessConfig::make();
        $this->engineConfig = FakeEngineConfig::make();
    }

    /**
     * Stand in for the missing test.sqlite fixture: build the KntKarty foreign source and
     * seed enough rows (2+ chunks) for the loader/parser tests. Deterministic sequential
     * keys avoid the primary-key collisions a random factory would risk at this volume.
     */
    protected function seedForeignSource(): void
    {
        $schema = Schema::connection('xl');

        if ($schema->hasTable('KntKarty')) {
            return;
        }

        // Columns are lower-cased: the parser reads them as $model->knt_gidnumer etc.,
        // which only resolves when SELECT * returns lower-cased attribute keys.
        $schema->create('KntKarty', function (Blueprint $table) {
            $table->unsignedBigInteger('knt_gidnumer')->primary();
            $table->string('knt_nazwa1');
            $table->string('knt_nazwa2');
            $table->string('knt_kraj');
            $table->string('knt_kodp');
            $table->string('knt_miasto');
            $table->string('knt_ulica');
            $table->string('knt_nip');
            $table->string('knt_powiat');
            $table->string('knt_wojewodztwo');
            $table->string('knt_telefon1');
            $table->string('knt_telefon2');
            $table->string('knt_email');
            $table->integer('knt_limitokres');
            $table->integer('knt_atrwlascicielfrsid');
            $table->integer('knt_openumer');
            $table->dateTime('knt_synctimestamp');
        });

        $syncTs = now()->subYear()->toDateTimeString();
        $rows = [];
        for ($i = 1; $i <= 500; $i++) {
            $rows[] = [
                'knt_gidnumer' => $i,
                'knt_nazwa1' => 'Nazwa ' . $i,
                'knt_nazwa2' => 'Nazwa2 ' . $i,
                'knt_kraj' => 'PL',
                'knt_kodp' => '80-000',
                'knt_miasto' => 'Miasto',
                'knt_ulica' => 'Ulica ' . $i,
                'knt_nip' => '12345678' . str_pad((string) ($i % 100), 2, '0', STR_PAD_LEFT),
                'knt_powiat' => 'Powiat',
                'knt_wojewodztwo' => 'Pomorskie',
                'knt_telefon1' => '58000' . $i,
                'knt_telefon2' => '60000' . $i,
                'knt_email' => 'kontakt' . $i . '@example.com',
                'knt_limitokres' => 30,
                'knt_atrwlascicielfrsid' => 1000 + $i,
                'knt_openumer' => 2000 + $i,
                'knt_synctimestamp' => $syncTs,
            ];
        }

        // Chunked to stay under SQLite's bound-parameter limit (~999).
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::connection('xl')->table('KntKarty')->insert($chunk);
        }
    }

    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.sync.timestamps', ['updatedAt']);
    }
}
