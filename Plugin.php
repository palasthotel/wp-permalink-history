<?php
/**
 * Plugin Name: Permalink History
 * Description: Saves a history of posts permalinks
 * Version: 1.0.3
 * Author: PALASTHOTEL (by Edward Bock)
 * Author URI: https://palasthotel.de
 * Text Domain: permalink-history
 * Domain Path: /languages
 */

namespace Palasthotel\PermalinkHistory;

/**
 * @property Database database
 * @property Post post
 * @property Migrate migrate
 * @property Redirects $redirects
 * @property Settings settings
 * @property string path
 * @property string url
 * @property MetaBox meta_box
 * @property TermTaxonomy term_taxonomy
 */
class Plugin {

	const DOMAIN = "permalink-history";

	const ACTION_REDIRECT_404 = "permalink_history_redirect_404";

	private function __construct() {

		$this->path = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);

		require_once dirname(__FILE__)."/vendor/autoload.php";

		$this->database  = new Database();
		$this->post      = new Post($this);
		$this->term_taxonomy = new TermTaxonomy($this);
		$this->migrate   = new Migrate();
		$this->redirects = new Redirects($this);
		$this->settings  = new Settings($this);
		$this->meta_box = new MetaBox($this);

		register_activation_hook(__FILE__, array($this, "on_activate"));
	}

	/**
	 * on plugin activation
	 */
	public function on_activate(){
		$this->database->create();
	}

	/**
	 * @var Plugin $instance
	 */
	private static $instance = null;

	/**
	 * @return Plugin
	 */
	static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

}

Plugin::get_instance();