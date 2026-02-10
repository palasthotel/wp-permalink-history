<?php
/**
 * Plugin Name: Permalink History
 * Description: Saves a history of post and page permalinks and adds redirects for old permalinks.
 * Version: 2.0.3
 * Author: PALASTHOTEL (by Edward Bock, Lucas Regalar, Jana Eggebrecht)
 * Author URI: https://palasthotel.de
 * Text Domain: permalink-history
 * Domain Path: /languages
 */

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\Assets;
use Palasthotel\PermalinkHistory\Components\TextdomainConfig;
use Palasthotel\PermalinkHistory\UseCase\FindRedirectUseCase;

require_once dirname( __FILE__ ) . "/vendor/autoload.php";

class Plugin extends Components\Plugin {

	const DOMAIN = "permalink-history";

	const ACTION_REDIRECT_404 = "permalink_history_redirect_404";
    const FILTER_FIND_REDIRECT_BEFORE = "permalink_history_find_redirect_before";
    const FILTER_FIND_REDIRECT_AFTER = "permalink_history_find_redirect_after";

	const HANDLE_SCRIPT_GUTENBERG = "permalink_history_js";
	const HANDLE_STYLE_GUTENBERG = "permalink_history_css";

	const REST_NAMESPACE_V1 = "permalink-history/v1";

    public Database $database;
    public Post $post;
    public Migrate $migrate;
    public Redirects $redirects;
    public Settings $settings;
    public string $path;
    public string $url;
    public TermTaxonomy $term_taxonomy;
    public Ajax $ajax;
    public FindRedirectUseCase $findRedirectsUseCase;
	public Assets $assets;

	public function onCreate() {

		$this->textdomainConfig = new TextdomainConfig(
			Plugin::DOMAIN,
			"languages"
		);

		$this->assets        = new Assets($this);
		$this->database      = new Database();
		$this->post          = new Post( $this );
		$this->term_taxonomy = new TermTaxonomy( $this );
		$this->migrate       = new Migrate();
		$this->redirects     = new Redirects( $this );
		$this->settings      = new Settings( $this );
		$this->ajax          = new Ajax( $this );

		new REST($this);
		new Gutenberg($this);

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

require_once __DIR__."/public-functions.php";
