<?php

namespace Grixu\Synchronizer\Console;

use Grixu\Synchronizer\Engine\Models\ExcludedField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddExcludedFieldCommand extends Command
{
    protected $signature = 'synchronizer:add';

    protected $description = 'Add a field to avoid in data sync';

    public function handle()
    {
        $model = $this->choice('Select model', $this->getModels(), 0);
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

    public function getModels(): array
    {
        return collect(File::allFiles(base_path()))
            ->filter(
                function ($item) {
                    return Str::contains($item, 'Models') && !Str::contains($item, 'vendor');
                }
            )
            ->map(
                function ($file) {
                    $src = file_get_contents($file->getRealPath());
                    if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
                        return $m[1] . '\\' . $file->getBasename('.php');
                    }

                    return null;
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }
}
