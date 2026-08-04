<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-08
 * Time: 19:34
 */

namespace Palasthotel\PermalinkHistory;

defined( 'ABSPATH' ) || exit;

use Palasthotel\PermalinkHistory\Components\Component;

class Settings extends Component {

	public function onCreate():void {
		add_action('admin_init', array($this,'custom_permalink_settings'));
	}

	/**
	 * register settings
	 */
	public function custom_permalink_settings() {
		add_settings_section(
			'permalink-history-settings', // ID
			// A literal text domain is required, otherwise the string is not
			// picked up when the translation template is generated.
			__( 'Permalink History', 'permalink-history' ), // Section title
			array($this, 'render'), // Callback for your function
			'permalink' // Location (Settings > Permalinks)
		);
	}

	/**
	 * render settings
	 */
	public function render(){
		// TODO: make this async call with paged redirects response and render it into a textarea
		printf(
			'<p><a href="%1$s" target="_blank" rel="noopener">%2$s</a></p>',
			esc_url( $this->plugin->redirects->getAjaxUrl() ),
			esc_html__( 'Generate redirects map (this can take a while)...', 'permalink-history' )
		);
	}
}
