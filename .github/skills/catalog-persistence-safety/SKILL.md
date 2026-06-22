---
name: catalog-persistence-safety
description: Bulk catalog persistence rules for xflickr_* tables.
---

# Catalog persistence safety

- Use `FlickrCatalogService` for all catalog writes from fetchers.
- Repositories expose `upsertMany()` — no per-row `save()` in fetchers.
- Pivots use `PivotRepository` with `insertOrIgnore()` in chunks.
- Photo pages bulk-upsert owners into contacts when saving photos.
- Use `DB::transaction()` only inside catalog service for multi-table photo+owner writes.
- Never delete catalog rows during normal crawl — upsert only.
