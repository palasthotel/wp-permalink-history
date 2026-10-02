# Contributing

The project has a [Code of Conduct](CODE_OF_CONDUCT.md) that all contributors are
expected to follow. Security issues are not reported as issues - see
[SECURITY.md](SECURITY.md).

## Branching

`main` is the default branch and always reflects what is released (or about to be
released). Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history, so commit messages
follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>

[optional body]

[optional footer]
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (2.0.5 → 2.0.6) | yes, "Bug Fixes" |
| `feat:` | minor (2.0.5 → 2.1.0) | yes, "Features" |
| `feat!:` or `BREAKING CHANGE:` footer | major (2.0.5 → 3.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `style:`, `test:`, `ci:` | none | no |

A pull request that should trigger a release needs at least one `fix:` or `feat:`
commit. When squash-merging, make sure the squash commit message itself is a
conventional commit — that is the message release-please reads.

### Which changes get `fix:` or `feat:`

Only changes that matter to someone using the plugin. `fix:` and `feat:` decide the
version *and* write the line that ends up in the changelog on the wordpress.org plugin
page, so the question to ask before committing is whether a user of the plugin would care
about that line.

Everything else takes a type that releases nothing — workflows and CI, release tooling,
repository documentation, internal refactoring, dependency updates of the build, and
anything touching files that are not shipped. As a rule of thumb, a change confined to
files outside `public/` is almost never a `fix:`. That includes hardening: blocking
direct access to a file that is not part of the download is `chore:`.

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/Plugin.php` | the plugin: header and bootstrap |
| `public/classes/` | the plugin's classes, autoloaded by composer (PSR-4, `Palasthotel\PermalinkHistory\`) |
| `public/vendor/` | the composer autoloader; the pack regenerates it without dev dependencies |
| `public/dist/` | the compiled editor panel - built, not in the repository |
| `public/languages/` | translations; `permalink-history.pot` is generated with `wp i18n make-pot` |
| `public/readme.txt` | the wordpress.org listing |
| `src/` | the editor panel in TypeScript |
| `assets/` | icon and screenshot of the wordpress.org plugin page, mirrored to SVN `assets/` |
| `Plugin.php` | development wrapper, loads `public/`; never deployed |

The main file `public/Plugin.php` must keep its name. WordPress identifies an installed
plugin by `<directory>/<main file>` and stores that pair in `active_plugins`; renaming it
deactivates the plugin on every site at the next update.

A class file has to be named exactly like the class, including case: the autoloader
looks the file up by the class name, and a Linux server does not find `Multisite.php`
for `MultiSite`.

## Local setup

```sh
npm ci
npm run build                 # compiles src/ into public/dist/ (npm run dev to watch)
npx @wordpress/env start      # http://localhost:8888, admin / password
```

`.wp-env.json` mounts `public/` as the plugin, so `npm run build` has to run before the
editor panel shows up. Set `WP_ENV_PORT` if port 8888 is taken. Permalinks have to be
set to anything but "Plain" - without a permalink structure the plugin records nothing.

`npm run lint` runs ESLint and `tsc --noEmit`; wp-scripts itself never checks the types.

`npm run pack` stages the payload in `build/permalink-history/` and zips it to
`permalink-history.zip` — the same payload the release deploys. It runs the shared script
from [palasthotel/github-workflows](https://github.com/palasthotel/github-workflows),
which has to be checked out next to this repository, and expects `npm run build` to have
run.

## Dependencies

All npm packages are `devDependencies`: nothing from `node_modules` ships, and the
`@wordpress/*` packages as well as React are externalised - the browser gets
WordPress core's copies. Update them together in one commit (`chore(deps): …`) and
verify with `npm ci && npm run lint && npm run build`; the dependency list in
`public/dist/gutenberg.ts.asset.php` shows whether anything changes at runtime.

## Versions

Never edit version numbers by hand. `package.json`, `CHANGELOG.md`, `public/Plugin.php`
and the `Stable tag:` in `public/readme.txt` are all maintained by the release pipeline —
see [.github/WORKFLOWS.md](.github/WORKFLOWS.md).

Content changes to `public/readme.txt` (description, FAQ, tested-up-to) are of course done
by hand; just leave `Stable tag:` and the `== Changelog ==` entries alone.

## Checks

Every PR runs `php -l` against PHP 8.0, 8.2, 8.3 and 8.4, builds and packs the plugin and
checks the payload, checks that the version carriers agree, and runs ESLint and `tsc`.
