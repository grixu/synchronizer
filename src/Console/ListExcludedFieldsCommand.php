<?php

namespace Grixu\Synchronizer\Console;

use Grixu\Synchronizer\Models\SynchronizerField;
use Illuminate\Console\Command;

/**
 * Class ListExcludedFieldsCommand
 * @package Grixu\Synchronizer\Console
 */
class ListExcludedFieldsCommand extends Command
{
    protected $signature = 'synchronizer:list';

    protected $description = 'List all transformers stored in DB';

    private int $count = 0;

    public function deleteEntry()
    {
        $models = SynchronizerField::query()->pluck('model')->unique()->toArray();
        $model = $this->choice('Choose model', $models);

        $entries = SynchronizerField::query()->where('model', $model)->pluck('field')->toArray();
        $entry = $this->choice('Choose field', $entries);

        SynchronizerField::query()->where(
            [
                ['model', '=', $model],
                ['field', '=', $entry]
            ]
        )->delete();
        $this->info('DELETED');

        $this->handleExtra();
    }

    public function handleExtra()
    {
        $choice = $this->choice('Would like to take some action like as:', ['exit', 'delete'], 0);

        if ($choice == 'delete') {
            $this-> deleteEntry();
        }

        return 0;
    }

    public function handle()
    {
        $this->count = SynchronizerField::query()->count();

        $this->info('List of Excluded fields:');

        $data = SynchronizerField::query()->orderBy('model')->select('id', 'field', 'model', 'update_empty')->get()->toArray();
        $this->table(['ID', 'Field name', 'Model', 'Update when empty?'], $data);

        if ($this->count === 0) {
            $this->info('Nothing found. Exiting.');
            return 0;
        }

        return $this->handleExtra();
    }
}
