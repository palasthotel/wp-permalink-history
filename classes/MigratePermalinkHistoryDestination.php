<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-10
 * Time: 17:33
 */

namespace Palasthotel\PermalinkHistory;

/**
 * @property Plugin plugin
 */
class MigratePermalinkHistoryDestination extends \ph_destination{

	/**
	 * MigratePermalinkHistoryDestination constructor.
	 *
	 */
	public function __construct() {
		$this->plugin = Plugin::get_instance();
	}

	public function createItem()
	{
		return new \stdClass();
	}

	public function getItemByID($id)
	{
		return $this->plugin->database->getById($id);
	}

	public function save($item)
	{
		if(
			isset($item->content_id)
			&&
			$item->content_id !== null
			&&
			!$this->plugin->database->permalinkHistoryExists( $item->content_id, $item->permalink, $item->content_type)
		){
			if($this->plugin->database->addPermalink($item->content_id, $item->content_type, $item->permalink)){
				return $this->plugin->database->wpdb->insert_id;
			}
		}
		return null;
	}

	public function deleteItem($item)
	{
		$this->plugin->database->deleteById($item->id);
	}
}