<?php
/**
 * Plugin Name: Permalink History
 * Description: Saves a history of posts permalinks
 * Version: 1.2.0
 * Author: PALASTHOTEL (by Edward Bock)
 * Author URI: https://palasthotel.de
 * Text Domain: permalink-history
 * Domain Path: /languages
 */

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\TextdomainConfig;
use Palasthotel\PermalinkHistory\UseCase\FindRedirectUseCase;

require_once dirname( __FILE__ ) . "/vendor/autoload.php";

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
 * @property Ajax $ajax
 * @property FindRedirectUseCase $findRedirectsUseCase
 */
class Plugin extends Components\Plugin {

	const DOMAIN = "permalink-history";

	const ACTION_REDIRECT_404 = "permalink_history_redirect_404";

	public function onCreate() {

		$this->textdomainConfig = new TextdomainConfig(
			Plugin::DOMAIN,
			"languages"
		);

		$this->database      = new Database();
		$this->post          = new Post( $this );
		$this->term_taxonomy = new TermTaxonomy( $this );
		$this->migrate       = new Migrate();
		$this->redirects     = new Redirects( $this );
		$this->settings      = new Settings( $this );
		$this->meta_box      = new MetaBox( $this );
		$this->ajax          = new Ajax( $this );

		$this->findRedirectsUseCase = new FindRedirectUseCase($this);

		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( "WP_CLI" ) ) {
			\WP_CLI::add_command( 'permalink-history', __NAMESPACE__ . '\WPCli' );
		}

	}

	/**
	 * on plugin activation
	 */
	public function onSiteActivation() {
		parent::onSiteActivation();
		$this->database->createTables();
	}

	/**
	 * @return mixed|Plugin
	 * @deprecated use Plugin::instance() instead
	 */
	public static function get_instance() {
		return self::instance();
	}

}

Plugin::instance();
