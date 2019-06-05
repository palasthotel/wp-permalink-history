<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-04
 * Time: 10:48
 */

namespace Palasthotel\PermalinkHistory;


/**
 * @property \wpdb wpdb
 * @property string $tablename
 */
class Database {

	const CONTENT_TYPE_POST = "post";

	const CONTENT_TYPE_TERM_TAXONOMY = "term_taxonomy";

	/**
	 * Database constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb      = $wpdb;
		$this->tablename = $wpdb->prefix . "permalink_history";
	}

	/**
	 * @param $content_id
	 * @param $content_type
	 * @param $permalink_without_domain
	 *
	 * @return false|int
	 */
	public function addPermalink( $content_id, $content_type, $permalink_without_domain ) {
		return $this->wpdb->insert(
			$this->tablename,
			array(
				"content_id"   => $content_id,
				"content_type" => $content_type,
				"permalink"    => $permalink_without_domain,
			),
			array(
				"%d",
				"%s",
				"%s",
			)
		);
	}

	/**
	 * add a post permalink to history
	 *
	 * @param $post_id
	 * @param $permalink_without_domain
	 *
	 * @return false|int
	 */
	public function addPostPermalink( $post_id, $permalink_without_domain ) {
		return $this->addPermalink( $post_id, self::CONTENT_TYPE_POST, $permalink_without_domain );
	}

	/**
	 * add a term permalink to history
	 *
	 * @param int $taxonomy_term_id
	 * @param string $permalink_without_domain
	 *
	 * @return false|int
	 */
	public function addTermTaxonomyPermalink( $taxonomy_term_id, $permalink_without_domain ) {
		return $this->addPermalink( $taxonomy_term_id, self::CONTENT_TYPE_TERM_TAXONOMY, $permalink_without_domain );
	}

	/**
	 * @param int $id
	 *
	 * @return HistoryItem|null
	 */
	public function getById( $id ) {
		return HistoryItem::parse( $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM $this->tablename+ WHERE id = %d", $id )
		) );
	}

	/**
	 * @param string $permalink_without_domain
	 * @param string $content_type
	 *
	 * @return int
	 */
	public function getId( $permalink_without_domain, $content_type ) {
		return intval( $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT content_id FROM $this->tablename WHERE permalink IN ( %s, %s, %s ) AND content_type = %s LIMIT 1",
				$permalink_without_domain,
				"/" . $permalink_without_domain,
				"/" . $permalink_without_domain . "/",
				$content_type
			)
		) );
	}

	/**
	 * @param string $permalink_without_domain
	 *
	 * @return int
	 */
	public function getPostId( $permalink_without_domain ) {
		return $this->getId( $permalink_without_domain, self::CONTENT_TYPE_POST );
	}

	/**
	 * @param string $permalink_without_domain
	 *
	 * @return int
	 */
	public function getTermTaxonomyId( $permalink_without_domain ) {
		return $this->getId( $permalink_without_domain, self::CONTENT_TYPE_TERM_TAXONOMY );
	}

	/**
	 * @param int $content_id
	 * @param string $permalink_without_domain
	 * @param string $content_type
	 *
	 * @return boolean
	 */
	public function permalinkHistoryExists( $content_id, $permalink_without_domain, $content_type ) {
		return intval( $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT count(id) FROM $this->tablename WHERE permalink IN ( %s, %s, %s ) AND content_type = %s AND content_id = %d",
					$permalink_without_domain,
					"/" . $permalink_without_domain,
					"/" . $permalink_without_domain . "/",
					$content_type,
					$content_id
				)
			) ) > 0;
	}

	/**
	 * @param int $post_id
	 * @param string $permalink_without_domain
	 *
	 * @return boolean
	 */
	public function postPermalinkHistoryExists($post_id, $permalink_without_domain ) {
		return $this->permalinkHistoryExists( $post_id, $permalink_without_domain, self::CONTENT_TYPE_POST );
	}

	/**
	 * @param int $term_taxonomy_id
	 * @param string $permalink_without_domain
	 *
	 * @return boolean
	 */
	public function termTaxonomyPermalinkHistoryExists( $term_taxonomy_id, $permalink_without_domain ) {
		return $this->permalinkHistoryExists( $term_taxonomy_id, $permalink_without_domain, self::CONTENT_TYPE_TERM_TAXONOMY );
	}

	/**
	 * @param int $limit
	 * @param int $page
	 *
	 * @return int[]
	 */
	public function getPostIdsWithNoHistory( $limit, $page = 0 ) {
		$offset = $limit * $page;
		$wpdb   = $this->wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND guid != '' AND post_name != ''
				AND ID NOT IN ( SELECT DISTINCT content_id FROM $this->tablename WHERE content_type = %s )
				LIMIT $offset, $limit",
				self::CONTENT_TYPE_POST
			)
		);
	}

	/**
	 * @param int $limit
	 * @param int $page
	 *
	 * @return int[]
	 */
	public function getTermTaxonomyIdsWithNoHistory( $limit, $page = 0 ) {
		$offset = $limit * $page;
		$wpdb   = $this->wpdb;

		// ⚠️ it is very important to join with wp_terms
		// because there were cases where there have been orphaned term_taxonomy entries
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT tt.term_taxonomy_id as id FROM $wpdb->terms as t LEFT JOIN $wpdb->term_taxonomy as tt ON (t.term_id = tt.term_id) 
				WHERE tt.term_taxonomy_id NOT IN ( 
				  SELECT DISTINCT content_id FROM $this->tablename WHERE content_type = %s 
				)
				LIMIT $offset, $limit",
				self::CONTENT_TYPE_TERM_TAXONOMY
			)
		);
	}

	/**
	 * @param int $content_id
	 * @param string $content_type
	 *
	 * @return HistoryItem[]
	 */
	public function getHistoryFor($content_id, $content_type){
		return array_map(
			function ($item) {
				return HistoryItem::parse($item);
			},
			$this->wpdb->get_results(
				$this->wpdb->prepare( "SELECT * FROM $this->tablename WHERE content_id = %d AND content_type = %s ORDER BY id", $content_id, $content_type )
			)
		);
	}

	/**
	 * @param $content_type
	 *
	 * @return HistoryItem[]
	 */
	public function getHistoryOf( $content_type ) {
		return array_map(
			function ($item) {
				return HistoryItem::parse($item);
			},
			$this->wpdb->get_results(
				$this->wpdb->prepare( "SELECT * FROM $this->tablename WHERE content_type = %s order by id", $content_type )
			)
		);
	}

	/**
	 * @return HistoryItem[]
	 */
	public function getPostHistory() {
		return $this->getHistoryOf( self::CONTENT_TYPE_POST );
	}

	/**
	 * @return HistoryItem[]
	 */
	public function getTermTaxonomyHistory() {
		return $this->getHistoryOf( self::CONTENT_TYPE_TERM_TAXONOMY );
	}

	/**
	 * @return string[]
	 */
	public function getContentTypes(){
		return $this->wpdb->get_col("SELECT DISTINCT content_type FROM $this->tablename");
	}

	/**
	 * @param string $content_type
	 *
	 * @return int
	 */
	public function getCount($content_type = ""){
		$where = "";
		if(!empty($content_type )) $where = $this->wpdb->prepare("WHERE content_type = %s", $content_type);
		return intval($this->wpdb->get_var("SELECT count(id) FROM $this->tablename $where"));
	}

	/**
	 * @param int $id
	 *
	 * @return false|int
	 */
	public function deleteById( $id ) {
		return $this->wpdb->delete(
			$this->tablename,
			array(
				"id" => $id,
			),
			array(
				'%d',
			)
		);
	}

	/**
	 * @param $content_id
	 *
	 * @return false|int
	 */
	public function deletePostPermalinkHistory($content_id){
		return $this->deletePermalinkHistory($content_id, self::CONTENT_TYPE_POST);
	}

	/**
	 * @param $content_id
	 *
	 * @return false|int
	 */
	public function deleteTermTaxonomyPermalinkHistory($content_id){
		return $this->deletePermalinkHistory($content_id, self::CONTENT_TYPE_TERM_TAXONOMY);
	}

	/**
	 * @param int $content_id
	 * @param string $content_type
	 *
	 * @return false|int
	 */
	public function deletePermalinkHistory($content_id, $content_type){
		return $this->wpdb->delete(
			$this->tablename,
			array(
				"content_id" => $content_id,
				"content_type" => $content_type,
			),
			array(
				"%d",
				"%s",
			)
		);
	}

	/**
	 * create tables
	 */
	public function create() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tablename (
			 id bigint(20) unsigned not null auto_increment,
			 content_id bigint(20) unsigned not null,
			 content_type varchar(100) not null,
			 permalink varchar(190) not null,
			 primary key (id),
			 key (content_id),
			 key (content_type),
			 key (permalink),
			 key content_permalink (permalink, content_type),
			 unique key id_permalink_content (content_id, permalink, content_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}

}