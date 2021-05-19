<?php

namespace Grixu\Synchronizer\Console;

use Grixu\Synchronizer\Engine\Models\ExcludedField;
use Illuminate\Console\Command;

class AddExcludedFieldCommand extends Command
{
    protected $signature = 'synchronizer:add';

    protected $description = 'Add a field to avoid in data sync';

    public function handle()
    {
        $model = $this->ask('Enter model name');
        $field = $this->ask('Now, enter field name');
        $update = $this->confirm('Update field when empty(null)?');

        ExcludedField::create(
            [
                'model' => $model,
                'field' => $field,
                'update_empty' => $update,
            ]
        );

        $this->info('Done!');

        return 0;
    }
}
