<?php

namespace Palasthotel\PermalinkHistory\Components;

abstract class Component {


	public function __construct(
		public \Palasthotel\PermalinkHistory\Plugin $plugin
	) {
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	public function onCreate(): void {
		// init your hooks and stuff
	}
}
