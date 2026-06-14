<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SeedModeAware
{
    protected function seedMode(): string
    {
        $mode = str_replace('-', '_', strtolower((string) config('seed.sync_mode', 'add_only')));

        return in_array($mode, ['add_only', 'sync'], true) ? $mode : 'add_only';
    }

    protected function isSyncMode(): bool
    {
        return $this->seedMode() === 'sync';
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function seedModel(string $modelClass, array $keys, array $values): Model
    {
        if ($this->isSyncMode()) {
            return $modelClass::query()->updateOrCreate($keys, $values);
        }

        return $modelClass::query()->firstOrCreate($keys, $values);
    }
}
