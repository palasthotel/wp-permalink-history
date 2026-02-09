# Permalink History Plugin for WordPress

## Description
Permalink History is a WordPress plugin that allows you to keep track of changes to your post and page permalinks. It provides a history of all the permalink changes made to your content and automatically adds redirects from old permalinks to the new ones, ensuring that your visitors and search engines can still access your content even after the permalinks have been changed.

## Features
- Tracks changes to post and page permalinks.
- Automatically creates redirects from old permalinks to new ones.
- Provides a history of permalink changes for each post and page.
- Easy to use interface for managing permalink history.

## Installation
1. Download the plugin from the WordPress plugin repository or clone the repository from GitHub.
2. Upload the plugin files to the `/wp-content/plugins/permalink-history` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. The plugin will start tracking permalink changes immediately after activation.

## Usage
1. To view the permalink history for a post or page, go to the edit screen of the post or page.
2. In the sidebar under the “Permalink History” section, you will see a list of all the previous permalinks for that post or page.
3. You can click on any of the previous permalinks to view the redirect settings or to manage the redirects.


## FAQ
### Does this work with changes made before plugin installation? =
Nope! You need to first install this plugin to start saving the history.

### Does this effect my site performance?
For small sites (less than 30.000 posts) there should be no performance issue. We use a custom table with good indexation to provide quick search access for historical permalinks.
But to be honest. Perhaps it could get an issue, yes. In this case, we suggest you export a redirect map on the permalinks settings page and make early redirects before php starts working.