---
name: queue-horizon-operations
description: Operating the xflickr queue and dispatch scheduler.
---

# Queue & Horizon operations

- Default queue: `config('xflickr-crawler.queue')` (`xflickr`).
- Register `Schedule::command('xflickr:dispatch')->everyMinute()`.
- Ensure a worker processes the `xflickr` queue (Horizon supervisor recommended).
- After deploy, restart Horizon when job classes change.
