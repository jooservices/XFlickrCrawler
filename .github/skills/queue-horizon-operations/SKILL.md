---
name: queue-horizon-operations
description: Operating the xflickr queue and dispatch scheduler in a shared host Horizon setup.
---

# Queue & Horizon operations

- Default queue name: `config('xflickr-crawler.queue')` (`xflickr` via `XFLICKR_QUEUE`).
- Uses host `QUEUE_CONNECTION` (typically `redis`) — no package-specific queue connection.
- Register `Schedule::command('xflickr:dispatch')->everyMinute()` in host `routes/console.php`.
- Add `xflickr` to a host Horizon supervisor (merged or dedicated). Published stub: `stubs/xflickr-horizon-supervisor.php`.
- `CACHE_STORE=redis` recommended for `ShouldBeUnique` job locks.
- Rate limits are per `connection_key`, not per subject NSID.
- After deploy, restart Horizon when job classes change.

Full checklist: [docs/01-getting-started/host-app-integration.md](../../docs/01-getting-started/host-app-integration.md).
