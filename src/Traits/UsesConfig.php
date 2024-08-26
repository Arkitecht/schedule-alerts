<?php

namespace Arkitecht\ScheduleFailureAlert\Traits;

use Illuminate\Support\Arr;

trait UsesConfig
{
    protected function getConfig(array $config = []): array
    {
        return array_merge(config('schedule-alerts', []), $config);
    }

    protected function getConfigValue(string $key, bool $required = true, mixed $default = null, array|null $config = []): mixed
    {
        $config = property_exists($this, 'config') ? $this->config : $config;

        if ($value = Arr::get($config, $key, $default)) {
            return $value;
        }

        if ($required) {
            throw new \Exception('Failure-alerts config value: ' . $key . ' is required and not provided');
        }

        return null;
    }
}
