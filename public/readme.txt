=== Permalink History ===
Contributors: edwardbock, palasthotel, lucasregalar
Donate link: https://palasthotel.de/
Tags: seo, permalink, backup, protocol, history
Requires at least: 5.0
Tested up to: 6.7.2
Stable tag: 1.3.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl

Saves the history of your WordPress site and provides automatic redirects.

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

Nobe! You need to first install this plugin to start saving the history.

= Does this effect my site performance? =

For small sites (less than 30.000 posts) there should be no performance issue. We use a custom table with good indexation to provide quick search access for historical permalinks.

But to be honest. Perhaps it could get an issue, yes. Therefore you export a redirect map on the permalinks settings page and make early redirects before php starts working.


== Screenshots ==

== Changelog ==

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
