# Template: Prepare Release Checklist

## Goal

Prepare a release candidate safely before creating a version tag.

## Steps

1. Confirm target version (`vX.Y.Z`) and scope.
2. Update `develop` and create `release/<version>` from the latest `develop`.
3. Update changelog, README, version references, and release metadata on `release/<version>` only.
4. Run local validation:

```bash
composer lint
composer test
```

5. Confirm no pending uncommitted changes.
6. Open the release PR from `release/<version>` into `master`.
7. Review notable changes for release notes.

## Exit Criteria

- Local gates are green.
- Version is approved.
- Release branch is ready for PR into `master`.
