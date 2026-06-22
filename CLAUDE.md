# Claude Code Instructions For `jooservices/xflickr-crawler`

Read [AGENTS.md](AGENTS.md) first.

When working in this repository:

- Prefer the smallest change that fits the existing package structure under `src/`.
- Match repository-native style; Pint is the formatting authority.
- Understand module ownership before editing (see `.github/skills/class-purpose-and-module-map/SKILL.md`).
- Stop and ask when requirements are unclear, conflicting, or impossible based on real code.
- Route tasks through `.github/skills/` instead of one generic workflow.
- Keep tests and docs in the same change when public behavior moves.
- Follow dto Git flow: normal work from `develop`, releases via `release/<version>` into `master`, then merge back to `develop`.
- Do not route security issues through public issues; follow [SECURITY.md](SECURITY.md).

Use `.github/skills/` for focused guidance on crawl pipeline, runtime config, migrations, queues, testing, and documentation sync.
