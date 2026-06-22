<?php

declare(strict_types=1);

/**
 * XFlickr Crawler — Horizon supervisor snippet for host config/horizon.php
 *
 * Option A: add 'xflickr' to an existing supervisor's queue array:
 *   'queue' => ['default', 'xflickr'],
 *
 * Option B: dedicated supervisor (recommended for tuning):
 */
return [
    'supervisor-xflickr' => [
        'connection' => 'redis',
        'queue' => [env('XFLICKR_QUEUE', 'xflickr')],
        'balance' => 'auto',
        'autoScalingStrategy' => 'time',
        'maxProcesses' => 3,
        'maxTime' => 0,
        'maxJobs' => 0,
        'memory' => 128,
        'tries' => 3,
        'timeout' => 120,
        'nice' => 0,
    ],
];
