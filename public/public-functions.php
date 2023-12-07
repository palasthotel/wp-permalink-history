<?php

use Palasthotel\PermalinkHistory\Plugin;

function permalink_history_plugin(): Plugin {
	return Plugin::instance();
}

function permalink_history_find_redirect_for_post(string $path): int {
	return permalink_history_plugin()->database->findId($path, permalink_history_plugin()->database::CONTENT_TYPE_POST);
}