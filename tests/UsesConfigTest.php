<?php

namespace Tests;

use Arkitecht\ScheduleFailureAlert\Traits\UsesConfig;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class UsesConfigTest extends TestCase
{
    #[Test]
    function does_get_empty_configuration()
    {
        $this->assertEmpty((new UsesConfigClass)->config());
        $this->assertEmpty((new UsesConfigClass)->config([]));
    }

    #[Test]
    function does_use_config_values_with_empty_configuration()
    {
        $config = (new UsesConfigClass)->config(['channels' => ['mail']]);

        $this->assertNotEmpty($config);
        $this->assertEquals($config, ['channels' => ['mail']]);
    }

    #[Test]
    #[DefineEnvironment('slackConfig')]
    function does_use_base_values_with_configuration()
    {
        $config = (new UsesConfigClass)->config();

        $this->assertNotEmpty($config);
        $this->assertEquals($config, [
            'channels'   => ['slack'],
            'notifiable' => User::class,
            'recipients' => ['aaron@arkideas.com'],
        ]);
    }

    #[Test]
    #[DefineEnvironment('slackConfig')]
    function does_use_override_values_with_configuration()
    {
        $config = (new UsesConfigClass)->config([
            'channels' => ['slack', 'mail'],
        ]);

        $this->assertNotEmpty($config);
        $this->assertEquals($config, [
            'channels'   => ['slack', 'mail'],
            'notifiable' => User::class,
            'recipients' => ['aaron@arkideas.com'],
        ]);
    }

    protected function slackConfig($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('schedule-alerts', [
                'channels'   => ['slack'],
                'notifiable' => User::class,
                'recipients' => ['aaron@arkideas.com'],
            ]);
        });
    }
}

class UsesConfigClass
{
    use UsesConfig;

    public function config(array $config = [])
    {
        return $this->getConfig($config);
    }
}
