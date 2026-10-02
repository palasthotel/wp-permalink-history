# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `permalink-history` |
| version file | `package.json` (`release-type: node`) - release-please bumps it, the scripts read it |
| build step | `npm ci && npm run build` - compiles `src/` into `public/dist/`, which is not in the repository |
| composer | `public/composer.json` only describes the autoloader; the pack regenerates `vendor/` without dev dependencies and drops `composer.json`/`composer.lock` from the payload |
| PHP | `php -l` runs on 8.0, 8.2, 8.3 and 8.4 - the code needs 8.0 |
| extra PR check | `lint`: ESLint and `tsc --noEmit` |
| required check | `Failure check` in `pr.yml` - the branch ruleset requires it by that name |
| SVN | `assets/` (icon, screenshot) is in the repository and mirrored to the plugin page |
