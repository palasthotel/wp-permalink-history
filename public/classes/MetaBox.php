<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-11
 * Time: 12:14
 */

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\Component;

class MetaBox extends Component {

	public function onCreate():void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
	}
	public function add_meta_boxes(){
		add_meta_box(
			'permalink-history',
			__( 'Permalink History', Plugin::DOMAIN ),
			array( $this, 'render' ),
			null,
			'advanced',
			'low'
		);
	}

	public function render(){
		$permalink = $this->plugin->post->getEscapedPermalink(get_the_ID());
		$history = array_filter($this->plugin->database->getHistoryFor(get_the_ID(), Database::CONTENT_TYPE_POST), function($item) use ($permalink){
			return $item->permalink !== $permalink;
		});

		if(count($history)){
			echo "<ul>";
			foreach ($history as $item){
				/**
				 * @var HistoryItem $item
				 */
				echo "<li><a href='$item->permalink'>$item->permalink</a></li>";
			}
			echo "</ul>";
		} else {
			echo "<p>";
			_e("No history yet.", Plugin::DOMAIN);
			echo "</p>";
		}

	}
}
