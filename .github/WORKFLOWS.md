# CI/CD Workflows

The plugin is versioned by [release-please](https://github.com/googleapis/release-please)
based on [conventional commits](https://www.conventionalcommits.org/):
`fix:` → patch, `feat:` → minor, `feat!:` / `BREAKING CHANGE:` → major.

Tag format: `v*` (e.g. `v2.0.4`).

---

## Overview

```
Push to main
    │
    ├──▶ [release-please.yml]
    │        Creates / updates the release PR
    │
    │    On release PR (opened / synchronize)
    ├──▶ [update-plugin-version.yml]
    │        Syncs public/Plugin.php Version + public/readme.txt
    │
    │    On push to main and on every PR
    └──▶ [main.yml]
             shared.lint.yml + shared.build.yml + "Failure check" gate


Merge release PR  →  release-please pushes tag v2.0.4 + creates GitHub Release
    │
    └── v*  ──▶ [wordpress-svn-release.yml]
                    Version check → npm ci → build → pack
                    → upload zip to the Release
                    → deploy public/ to WordPress.org SVN
```

---

## Workflows

### `main.yml` — PR and branch checks

**Trigger:** Push to `main`, any pull request against `main`, `workflow_dispatch`

Calls `shared.lint.yml` (ESLint) and `shared.build.yml` (`npm run build`). The
`mergeable` job is the aggregated "Failure check" that the branch ruleset
requires — do not rename it.

---

### `release-please.yml` — Release PR management

**Trigger:** Push to `main`
**Token:** installation token of the org-owned *Palasthotel Release Bot* GitHub
App, minted per run by `actions/create-github-app-token`. Required because
`GITHUB_TOKEN` pushes do not trigger downstream workflows — the tag would never
start `wordpress-svn-release.yml`.

```
Push to main
      │
      ▼
  release-please
      │
      └──▶ opens / updates PR  "chore(main): release 2.0.4"
                bumps package.json + package-lock.json
                bumps .release-please-manifest.json
                updates CHANGELOG.md

  PR merged
      └──▶ pushes tag v2.0.4
           creates GitHub Release
```

> release-please owns `CHANGELOG.md`. Do not add notes to that file by hand: it
> prepends its own title and demotes an existing one, so hand-written content
> ends up below the release entries.

---

### `update-plugin-version.yml` — Plugin version files

**Trigger:** `pull_request` on `main` — types: `opened`, `synchronize`
**Condition:** Only runs for release-please PRs (`release-please--*`) whose head
branch lives in this repository
**Token:** app installation token — pushing with it re-runs the PR checks on the
new head commit, and `bin/update-plugin-version.sh` is idempotent, so the
resulting `synchronize` event is a no-op instead of a loop

```
Release PR opened / updated
              │
              ▼
    bash bin/update-plugin-version.sh
              │
              ├── reads version from package.json
              ├── updates "Version:" header in public/Plugin.php
              ├── updates "Stable tag:" in public/readme.txt
              └── prepends new "= x.y.z =" section to the readme changelog
              │
              ▼
    git commit + push → back onto the release PR branch
```

---

### `wordpress-svn-release.yml` — Deploy to WordPress.org

**Trigger:** Push of a `v*` tag

```
Tag: v2.0.4
      │
      ├── strip prefix → VERSION=2.0.4
      │
      ├── bin/version-checker.sh
      │       package.json == readme.txt Stable tag == Plugin.php Version == tag
      │       mismatch → job fails before anything is published
      │
      ├── npm ci → npm run build   (compiles src/ into public/dist/)
      │
      ├── npm run pack   (bin/build-plugin.sh)
      │       rsync public/ → build/permalink-history/
      │       composer install + dump-autoload, drop composer files
      │       zip → permalink-history.zip
      │
      ├──▶ Upload permalink-history.zip to the GitHub Release
      │
      ├── svn checkout  $SVN_REPO_URL  →  ./svn/
      │
      └── SVN commit
              rm trunk/*  +  rm tags/$VERSION
              cp public/* → trunk/  +  tags/$VERSION/
              svn add --force .
              svn rm deleted files
              svn commit "Release version $VERSION"
```

---

## Required secrets / variables

| Name | Type | Level | Value |
|---|---|---|---|
| `RELEASE_BOT_APP_ID` | variable | org | App ID of the *Palasthotel Release Bot* GitHub App |
| `RELEASE_BOT_PRIVATE_KEY` | secret | org | that app's private key (full `.pem`, incl. BEGIN/END lines) |
| `SVN_USERNAME` | secret | org | WordPress.org committer |
| `SVN_PASSWORD` | secret | org | WordPress.org password |
| `SVN_REPO_URL` | variable | repo | `https://plugins.svn.wordpress.org/permalink-history` |

The GitHub App needs to be installed on this repository with
`Contents: read & write` and `Pull requests: read & write`. `SVN_REPO_URL` is
repo-level because the slug differs per plugin; everything else is shared across
all plugin repos.

release-please never pushes to `main` — it opens a pull request — so a branch
ruleset on `main` needs no exception for the app. Add the app as a bypass actor
only if one of these applies: a **tag** ruleset restricts creating `v*` tags, a
ruleset also covers the `release-please--*` branches and forbids direct pushes,
or signed commits are required. In those cases the bot cannot tag the release or
update its own release PR.

---

## Files the release touches

| File | Updated by | Purpose |
|---|---|---|
| `package.json`, `package-lock.json` | release-please | version source |
| `.release-please-manifest.json` | release-please | last released version |
| `CHANGELOG.md` | release-please | GitHub-facing changelog |
| `public/Plugin.php` | `bin/update-plugin-version.sh` | `Version:` plugin header |
| `public/readme.txt` | `bin/update-plugin-version.sh` | `Stable tag:` + `== Changelog ==` |
