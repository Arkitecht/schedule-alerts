<?php

namespace Arkitecht\ScheduleFailureAlert\Notifications;

use Arkitecht\ScheduleFailureAlert\Traits\UsesConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

class ScheduleFailedNotification extends Notification
{
    use Queueable;
    use UsesConfig;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $className, public string $failureMessage, protected array $config)
    {
        $this->config = $this->getConfig($config);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->getConfigValue('channels');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getConfigValue('mail-subject', false, 'Failure in scheduled script');

        return (new MailMessage)
            ->error()
            ->subject($subject)
            ->line('There was a failure in your scheduled script: ' . $this->className)
            ->line($this->failureMessage);
    }


    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->sectionBlock(fn(SectionBlock $block) => $block->text('*There was a failure in your scheduled script: ' . $this->className . '*')->markdown())
            ->sectionBlock(fn(SectionBlock $block) => $block->text($this->failureMessage));
    }
}
