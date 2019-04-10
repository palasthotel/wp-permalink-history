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
	 * @param string $permalink_without_domain
	 * @param string $content_type
	 *
	 * @return boolean
	 */
	public function permalinkHistoryExists( $permalink_without_domain, $content_type ) {
		return intval( $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT count(id) FROM $this->tablename WHERE permalink IN ( %s, %s, %s ) AND content_type = %s",
					$permalink_without_domain,
					"/" . $permalink_without_domain,
					"/" . $permalink_without_domain . "/",
					$content_type
				)
			) ) > 0;
	}

	/**
	 * @param string $permalink_without_domain
	 *
	 * @return boolean
	 */
	public function postPermalinkHistoryExists( $permalink_without_domain ) {
		return $this->permalinkHistoryExists( $permalink_without_domain, self::CONTENT_TYPE_POST );
	}

	/**
	 * @param string $permalink_without_domain
	 *
	 * @return boolean
	 */
	public function termTaxonomyPermalinkHistoryExists( $permalink_without_domain ) {
		return $this->permalinkHistoryExists( $permalink_without_domain, self::CONTENT_TYPE_TERM_TAXONOMY );
	}

	/**
	 * @param string $permalink
	 *
	 * @return boolean
	 */
	public function postPermalinkHistoryNotExists( $permalink ) {
		return ! $this->postPermalinkHistoryExists( $permalink );
	}

	/**
	 * @param string $permalink
	 *
	 * @return bool
	 */
	public function termTaxonomyLinkHistoryNotExists( $permalink ) {
		return ! $this->termTaxonomyPermalinkHistoryExists( $permalink );
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

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->term_relationships WHERE 
				term_taxonomy_id NOT IN ( SELECT DISTINCT term_taxonomy_id FROM $this->tablename WHERE content_type = %s )
				LIMIT $offset, $limit",
				self::CONTENT_TYPE_TERM_TAXONOMY
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
				$this->wpdb->prepare( "SELECT * FROM $this->tablename WHERE content_type = %s", $content_type )
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
	 * create tables
	 */
	public function create() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tablename (
			 id bigint(20) unsigned not null auto_increment,
			 content_id bigint(20) unsigned not null,
			 content_type varchar(100) not null,
			 permalink varchar(255) not null,
			 primary key (id),
			 key (content_id),
			 key (content_type),
			 key (permalink),
			 unique key content_permalink (content_id, permalink, content_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}

}