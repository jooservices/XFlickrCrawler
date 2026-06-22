<?php

declare(strict_types=1);

/**
 * XFlickr Crawler — add to host routes/console.php
 *
 * Required for pagination follow-up targets after page 1.
 */
use Illuminate\Support\Facades\Schedule;

Schedule::command('xflickr:dispatch')->everyMinute();
