<?php
/**
 * Plugin Name: Permalink History
 * Description: Saves a history of posts permalinks
 * Version: 0.2
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
 * @property Redirect redirect
 * @property Settings settings
 */
class Plugin {

	private function __construct() {

		require_once dirname(__FILE__)."/vendor/autoload.php";

		$this->database = new Database();
		$this->post = new Post($this);
		$this->migrate = new Migrate();
		$this->redirect = new Redirect($this);
		$this->settings = new Settings($this);

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