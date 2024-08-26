<?php

namespace Tests;

use Arkitecht\ScheduleFailureAlert\Notifications\ScheduleFailedNotification;
use Arkitecht\ScheduleFailureAlert\Traits\SendsScheduleFailureAlerts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class SendsScheduleFailureAlertsTest extends TestCase
{
    #[Test]
    function fails_without_config()
    {
        $failureClass = new FailableCommand();
        try {
            $failureClass->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Failure-alerts config value: notifiable is required and not provided', $e->getMessage());

            return;
        }

        $this->fail('No exception was thrown for missing config (notifiable)');
    }


    #[Test]
    #[DefineEnvironment('missingChannels')]
    function fails_without_channels()
    {
        $failureClass = new FailableCommand();
        try {
            $failureClass->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Failure-alerts config value: channels is required and not provided', $e->getMessage());

            return;
        }

        $this->fail('No exception was thrown for missing config (channels)');
    }

    #[Test]
    #[DefineEnvironment('mailConfig')]
    function sends_mail()
    {
        Notification::fake();
        /*$mailer = \Mockery::mock(\Illuminate\Contracts\Mail\Mailer::class);
        $this->app->instance('mailer', $mailer);*/

        $failureClass = new FailableCommand();
        $failureClass->fail();


        Notification::assertSentTo(User::make(['email' => 'aaron@arkideas.com']), ScheduleFailedNotification::class, function ($notification, $channels, $user)  {
            $this->assertEquals('aaron@arkideas.com', $user->email);
            $this->assertEquals(['mail'], $channels);

            return true;
        });
    }

    #[Test]
    #[DefineEnvironment('slackConfig')]
    function sends_slack()
    {
        Notification::fake();
        $failureClass = new FailableCommand();
        $failureClass->fail();
        Notification::assertSentTo(User::make(['email' => 'aaron@arkideas.com']), ScheduleFailedNotification::class, function ($notification, $channels) {
            $this->assertEquals(['slack'], $channels);

            return true;
        });
    }

    protected function missingChannels($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('schedule-alerts', [
                'notifiable' => User::class,
                'recipients' => ['aaron@arkideas.com'],
            ]);
        });
    }

    protected function mailConfig($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('schedule-alerts', [
                'channels'     => ['mail'],
                'notifiable'   => User::class,
                'recipients'   => ['aaron@arkideas.com'],
                'mail-subject' => 'Test failure message',
            ]);
        });
    }

    protected function slackConfig($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('services', [
                'slack' => [
                    'notifications' => [
                        'bot_user_oauth_token' => 'secret-token',
                        'channel'              => '#failure-channel',
                    ],
                ],
            ]);
            $config->set('schedule-alerts', [
                'channels'   => ['slack'],
                'notifiable' => User::class,
                'recipients' => ['aaron@arkideas.com'],
            ]);
        });
    }

    protected function multiConfig($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('services', [
                'slack' => [
                    'notifications' => [
                        'bot_user_oauth_token' => 'secret-token',
                        'channel'              => '#failure-channel',
                    ],
                ],
            ]);
            $config->set('schedule-alerts', [
                'channels'   => ['slack','mail'],
                'notifiable' => User::class,
                'recipients' => ['aaron@arkideas.com','baaron@arkideas.com'],
            ]);
        });
    }
}

class User extends Model
{
    use Notifiable;
    protected $fillable = ['email'];
}

class FailableCommand
{
    use SendsScheduleFailureAlerts;

    public function fail()
    {
        $this->sendFailureAlert('I failed :(');
    }

    public function error($errorMessage)
    {
        print "{$errorMessage}\n";
    }
}
