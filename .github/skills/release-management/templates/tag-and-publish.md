# Template: Tag and Publish

## Goal

Create a release tag and trigger automated release and publish flow.

## Steps

1. Confirm the approved release PR has been merged into `master`.
2. Checkout and update `master`:

```bash
git checkout master
git pull origin master
```

3. Create tag:

```bash
git tag vX.Y.Z
```

4. Push tag:

```bash
git push origin vX.Y.Z
```

5. Monitor `.github/workflows/release.yml` run.
6. Verify GitHub release notes and artifacts.
7. Verify package appears on Packagist.
8. Merge `master` back into `develop`.

## Exit Criteria

- Release workflow is successful.
- GitHub release is present.
- Packagist version is updated.
- `develop` has been synchronized with `master`.
