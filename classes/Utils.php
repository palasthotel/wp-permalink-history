<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-11
 * Time: 13:21
 */

namespace Palasthotel\PermalinkHistory;

/**
 * Class Utils
 *
 * @package Palasthotel\PermalinkHistory
 */
class Utils {

	/**
	 * @param string $url
	 *
	 * @return mixed
	 */
	static function getUrlPath($url){
		var_dump($url);
		return parse_url( $url,  PHP_URL_PATH);
	}
}