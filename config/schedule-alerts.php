<?php

return [
    'notifiable'    => env('SCHEDULE_ALERT_NOTIFIABLE'),
    'channels'      => env('SCHEDULE_ALERT_CHANNELS', []),
    'recipients'    => env('SCHEDULE_ALERT_RECIPIENTS', []),
    'slack-channel' => '#bugsnag',
];
