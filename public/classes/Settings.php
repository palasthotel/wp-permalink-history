<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-08
 * Time: 19:34
 */

namespace Palasthotel\PermalinkHistory;

class Settings {

	public Plugin $plugin;

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('admin_init', array($this,'custom_permalink_settings'));
	}

	/**
	 * register settings
	 */
	public function custom_permalink_settings() {
		add_settings_section(
			'permalink-history-settings', // ID
			__('Permalink History', Plugin::DOMAIN), // Section title
			array($this, 'render'), // Callback for your function
			'permalink' // Location (Settings > Permalinks)
		);
	}

	/**
	 * render settings
	 */
	public function render(){
		// TODO: make this async call with paged redirects response and render it into a textarea
		$url = $this->plugin->redirects->ajaxurl;
		echo "<p><a href='$url' target='_blank'>";
		_e("Generate redirects map (this can take a while)...", Plugin::DOMAIN);
		echo "</a></p>";
	}
}