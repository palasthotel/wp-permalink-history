<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-08
 * Time: 19:34
 */

namespace Palasthotel\PermalinkHistory;


/**
 * @property Plugin plugin
 */
class Settings {

	/**
	 * Settings constructor.
	 *
	 * @param Plugin $plugin
	 */
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
			'Permalink History', // Section title
			array($this, 'render'), // Callback for your function
			'permalink' // Location (Settings > Permalinks)
		);
	}

	/**
	 * render settings
	 */
	public function render(){
		?>
		<textarea style="width:100%;height:200px"><?php $this->plugin->redirect->renderRedirectMap(); ?></textarea>
		<?php
	}
}