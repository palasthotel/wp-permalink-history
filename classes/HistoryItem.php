<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-10
 * Time: 18:02
 */

namespace Palasthotel\PermalinkHistory;


/**
 * @property int|null id
 * @property int content_id
 * @property string content_type
 * @property string permalink
 */
class HistoryItem {

	/**
	 * HistoryItem constructor.
	 *
	 * @param int $content_id
	 * @param string $content_type
	 * @param string $permalink
	 */
	private function __construct($content_id, $content_type, $permalink, $id) {
		$this->id = $id;
		$this->content_id = $content_id;
		$this->content_type = $content_type;
		$this->permalink = $permalink;
	}

	/**
	 * @param $content_id
	 * @param $content_type
	 * @param $permalink
	 * @param null $id
	 *
	 * @return HistoryItem
	 */
	public static function build($content_id, $content_type, $permalink, $id = null){
		return new HistoryItem($content_id, $content_type, $permalink, $id);
	}

	/**
	 * @param object|null $resultObject
	 *
	 * @return HistoryItem
	 */
	public static function parse($resultObject){
		return (!is_object($resultObject))?
			null
			:
			self::build($resultObject->content_id, $resultObject->content_type, $resultObject->permalink, $resultObject->id);
	}
}