<?php

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\Component;

class Gutenberg extends Component {
	public function onCreate(): void {
		parent::onCreate();

		add_action('init', [$this, 'init']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
	}

	public function init(): void {
		$this->plugin->assets->registerScript(
			Plugin::HANDLE_SCRIPT_GUTENBERG,
			"dist/gutenberg.ts.js",
		);
	}

	public function enqueue_block_editor_assets(): void {
		wp_enqueue_script(Plugin::HANDLE_SCRIPT_GUTENBERG);
	}
}
