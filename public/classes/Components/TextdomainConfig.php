<?php


namespace Palasthotel\PermalinkHistory\Components;


/**
 * @version 0.1.1
 */
class TextdomainConfig {

    public string $domain;
    public string $languages;

	public function __construct(string $domain, string $relativeLanguagesPath) {
		$this->domain = $domain;
		$this->languages = $relativeLanguagesPath;
	}
}