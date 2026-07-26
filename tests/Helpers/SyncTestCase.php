<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ProcessConfigInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncTestCase extends TestCase
{
    protected ProcessConfigInterface $processConfig;
    protected EngineConfigInterface $engineConfig;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

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

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_customers_table.stub';
        try {
            (new \CreateCustomersTable())->up();
        } catch (\Throwable) {
        }

        $this->artisan('migrate')->run();

        $this->ensureJobBatchesTable();
        $this->seedForeignSource();

        $this->processConfig = FakeProcessConfig::make();
        $this->engineConfig = FakeEngineConfig::make();
    }

    /**
     * Created here rather than via queue:batches-table: publishing that migration duplicates
     * the stub in the testbench skeleton, so the next migrate fails with "table already exists".
     */
    protected function ensureJobBatchesTable(): void
    {
        if (Schema::hasTable('job_batches')) {
            return;
        }

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

    protected function seedForeignSource(): void
    {
        $this->createForeignSourceTable();

        // Chunked to stay under SQLite's bound-parameter limit (~999).
        foreach (array_chunk($this->foreignSourceRows(), 50) as $chunk) {
            DB::connection('xl')->table('KntKarty')->insert($chunk);
        }
    }

    protected function createForeignSourceTable(): void
    {
        // Casing matches FakeForeignSqlSourceModel: SQLite hands back attribute keys as
        // declared here, so lower-casing them would silently unbind every cast on the model.
        Schema::connection('xl')->create('KntKarty', function (Blueprint $table) {
            $table->unsignedBigInteger('Knt_GIDNumer')->primary();
            $table->string('Knt_Nazwa1');
            $table->string('Knt_Nazwa2');
            $table->string('Knt_Kraj');
            $table->string('Knt_KodP');
            $table->string('Knt_Miasto');
            $table->string('Knt_Ulica');
            $table->string('Knt_Nip');
            $table->string('Knt_Powiat');
            $table->string('Knt_Wojewodztwo');
            $table->string('Knt_Telefon1');
            $table->string('Knt_Telefon2');
            $table->string('Knt_EMail');
            $table->integer('Knt_LimitOkres');
            $table->integer('Knt_AtrWlascicielFrsID');
            $table->integer('Knt_OpeNumer');
            $table->dateTime('Knt_SyncTimeStamp');
        });
    }

    /**
     * Two full chunks at the default chunk size, so the loader tests have a second page to
     * page into. Sequential keys rather than the factory — random ones collide on the
     * primary key at this volume.
     */
    protected function foreignSourceRows(): array
    {
        $syncTs = now()->subYear()->toDateTimeString();
        $rowCount = 2 * config('synchronizer.sync.default_chunk_size');
        $rows = [];
        for ($i = 1; $i <= $rowCount; $i++) {
            $rows[] = [
                'Knt_GIDNumer' => $i,
                'Knt_Nazwa1' => 'Nazwa ' . $i,
                'Knt_Nazwa2' => 'Nazwa2 ' . $i,
                'Knt_Kraj' => 'PL',
                'Knt_KodP' => '80-000',
                'Knt_Miasto' => 'Miasto',
                'Knt_Ulica' => 'Ulica ' . $i,
                'Knt_Nip' => '12345678' . str_pad((string) ($i % 100), 2, '0', STR_PAD_LEFT),
                'Knt_Powiat' => 'Powiat',
                'Knt_Wojewodztwo' => 'Pomorskie',
                'Knt_Telefon1' => '58000' . $i,
                'Knt_Telefon2' => '60000' . $i,
                'Knt_EMail' => 'kontakt' . $i . '@example.com',
                'Knt_LimitOkres' => 30,
                'Knt_AtrWlascicielFrsID' => 1000 + $i,
                'Knt_OpeNumer' => 2000 + $i,
                'Knt_SyncTimeStamp' => $syncTs,
            ];
        }

        return $rows;
    }

    /**
     * Overrides the parent's snake_case list: Map::add() matches these against DTO field
     * names, which are camelCase.
     */
    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.sync.timestamps', ['updatedAt']);
    }
}
