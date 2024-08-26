<?php

namespace Arkitecht\ScheduleFailureAlert\Traits;

use Arkitecht\ScheduleFailureAlert\Notifications\ScheduleFailedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

trait SendsScheduleFailureAlerts
{
    use UsesConfig;

    public function sendFailureAlert(string $failureMessage, array $config = [])
    {
        $config = $this->getConfig($config);

        $notifiable = $this->getConfigValue(
            key: 'notifiable',
            config: $config
        );

        $channels = $this->getConfigValue(
            key: 'channels',
            config: $config
        );

        if (method_exists($this, 'error')) {
            $this->error($failureMessage);
        }

        //do this for all but slack
        $recipients = collect($this->getConfigValue('recipients', false, [], $config))
            ->map(fn($recipient) => new $notifiable(['email' => $recipient]));

        $slackIndex = array_search('slack', $channels);

        if ($slackIndex !== false) {
            $config['channels'] = ['slack'];

            Notification::send(
                $recipients->first(),
                new ScheduleFailedNotification(get_class($this), $failureMessage, $config)
            );

            array_splice($channels, $slackIndex, 1);
        }

        if (!count($channels)) {
            return Command::FAILURE;
        }

        $config['channels'] = $channels;
        Notification::send(
            $recipients,
            new ScheduleFailedNotification(get_class($this), $failureMessage, $config)
        );

        return Command::FAILURE;
    }

    /*
     *
     ** TODO
    protected function sendDirectFailureAlert(string $failureMessage, array $config = [])
    {
        Notification::route('mail', 'taylor@example.com')
            ->route('vonage', '5555555555')
            ->route('slack', '#slack-channel')
            ->route('broadcast', [new Channel('channel-name')])
            ->notify(new InvoicePaid($invoice));
    }
    */

}
