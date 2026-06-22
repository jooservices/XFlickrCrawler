---
name: database-migration-safety
description: Safe migration practices for xflickr_* tables.
---

# Database migration safety

- Prefer additive migrations (new tables/columns/indexes).
- Keep reversible `down()` methods when practical.
- Use configurable table names via `config/xflickr-crawler.tables.*`.
- Do not run data backfills for large datasets inside migrations.
