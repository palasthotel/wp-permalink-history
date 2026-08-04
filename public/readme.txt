=== Permalink History ===
Contributors: edwardbock, palasthotel, lucasregalar, janaeggebrecht
Donate link: https://palasthotel.de/
Tags: seo, permalink, backup, protocol, history
Requires at least: 6.6
Tested up to: 7.0.2
Requires PHP: 8.0
Stable tag: 2.0.5
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Saves the history of your WordPress site permalinks and provides automatic redirects.

== Description ==

Saves the history of your WordPress site and provides automatic redirects.

== Installation ==

1. Upload `permalink-history.zip` to the `/wp-content/plugins/` directory
1. Extract the Plugin to a `permalink-history` Folder
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How does it work? =

This plugin tries to save all changes of permalinks in your system. When someone uses an old permalink that is in the history database it will not show 404 page but instead redirect to the new location.

= Does this work with changes made before plugin installation? =

Nope! You need to first install this plugin to start saving the history.

= Does this effect my site performance? =

For small sites (less than 30.000 posts) there should be no performance issue. We use a custom table with good indexation to provide quick search access for historical permalinks.

But to be honest. Perhaps it could get an issue, yes. Therefore you export a redirect map on the permalinks settings page and make early redirects before php starts working.


== Screenshots ==

1. The Permalink History panel in the editor sidebar: every path this content was reachable under before. Unchecking one and saving deletes that redirect for good.

== Changelog ==

= 2.0.5 =
**Bug Fixes**
* restore the site - the nonce was created before WordPress could (8a5f4a6)
* restore the site - the nonce was created before WordPress could (4f4fa9f)

= 2.0.4 =
**Bug Fixes**
* bind the LIKE term and LIMIT values in database queries (975aa76)
* block direct file access and use a literal text domain (05b34e5)
* change minimum version to 6.6 (eee78db)
* declare the PHP 8.0 requirement the code already has (08e79e1)
* only expose publicly visible content through the public history endpoints (9660279)
* require manage_options and a nonce for the redirect map (026990f)
* verify history rows belong to the post before deleting them (4cbce86)

= 2.0.3 =
* Fix: Gutenberg error fix for old history entries

= 2.0.2 =
* Fix: no css error fix

= 2.0.1 =
* Fix: Gutenberg panel produce js error in non-public post types

= 2.0.0 =
* Breaking: Dropped support for meta box (classic editor)
* Feature: Gutenberg document settings panel for permalink history
* Feature: full permalink history endpoint
* Fix: only save permalinks of publish posts

= 1.4.0 =
* Feature: get history by content id ajax endpoint
* Fix: database getById sql

= 1.3.2 =
* Fix: meta box for all other post types than post itself

= 1.3.1 =
* Php 8.2 compatibility warning fix

= 1.3.0 =
* Added filters for find redirect use case extensions

= 1.2.0 =
* Added admin ajax endpoint for permalink history mapping

= 1.1.0 =
* Optimization: multisite compatibility

= 1.0.5 =
* Optimization: Performance optimization by reducing redundant function calls
* Bugfix: Migrate field handler did not clean up old permalinks on update migration. It does now

= 1.0.4 =
* Feature: WP Cli extension

= 1.0.3 =
* Optimization: on check for redirect in frontend (!is_admin())
* Bugfix: only redirect posts with status publish

= 1.0.2 =
* Bugfix: Was redirecting too early. Works now with amp plugin.

= 1.0.1 =
* Feature: Action "permalink_history_redirect_404" if permalinks could not help you can try it yourself

= 1.0.0 =
* First release
