<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-04
 * Time: 10:48
 */

namespace Palasthotel\PermalinkHistory;

defined( 'ABSPATH' ) || exit;

class Database extends Components\Database {

	const CONTENT_TYPE_POST = "post";

	const CONTENT_TYPE_TERM_TAXONOMY = "term_taxonomy";


    public string $tablename;
	/**
	 * Database constructor.
	 */
	public function init() {
		$this->tablename = $this->wpdb->prefix . "permalink_history";
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

	public function getById( int $id ) {
		$wpdb = $this->wpdb;

		return HistoryItem::parse( $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM %i WHERE id = %d", $this->tablename, $id )
		) );
	}

	/**
	 * @param string $permalink_without_domain
	 * @param string $content_type
	 *
	 * @return int
	 */
	public function getId( $permalink_without_domain, $content_type ) {
		$wpdb = $this->wpdb;

		return intval( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT content_id FROM %i WHERE permalink IN ( %s, %s, %s, %s ) AND content_type = %s ORDER BY id DESC LIMIT 1",
				$this->tablename,
				$permalink_without_domain,
				"/" . $permalink_without_domain,
				$permalink_without_domain."/",
				"/" . $permalink_without_domain . "/",
				$content_type
			)
		) );
	}

	public function findId(string $path, string $content_type): int {
		// The LIKE term has to be a bound argument. Interpolating it into the
		// query leaves quotes unescaped (esc_like only handles _ % \) and any
		// path starting with s, d or f would form a bogus placeholder.
		$wpdb = $this->wpdb;

		return intval( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT content_id FROM %i WHERE permalink LIKE %s AND content_type = %s ORDER BY id DESC LIMIT 1",
				$this->tablename,
				'%' . $wpdb->esc_like( $path ) . '%',
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
		$wpdb = $this->wpdb;

		return intval( $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(id) FROM %i WHERE permalink IN ( %s, %s, %s, %s ) AND content_type = %s AND content_id = %d",
					$this->tablename,
					$permalink_without_domain,
					"/" . $permalink_without_domain,
					$permalink_without_domain. "/",
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
				"SELECT ID FROM %i WHERE post_status = 'publish' AND guid != '' AND post_name != ''
				AND ID NOT IN ( SELECT DISTINCT content_id FROM %i WHERE content_type = %s )
				LIMIT %d, %d",
				$wpdb->posts,
				$this->tablename,
				self::CONTENT_TYPE_POST,
				$offset,
				$limit
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
				"SELECT tt.term_taxonomy_id as id FROM %i as t LEFT JOIN %i as tt ON (t.term_id = tt.term_id)
				WHERE tt.term_taxonomy_id NOT IN (
				  SELECT DISTINCT content_id FROM %i WHERE content_type = %s
				)
				LIMIT %d, %d",
				$wpdb->terms,
				$wpdb->term_taxonomy,
				$this->tablename,
				self::CONTENT_TYPE_TERM_TAXONOMY,
				$offset,
				$limit
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
		$wpdb = $this->wpdb;

		return array_map(
			function ($item) {
				return HistoryItem::parse($item);
			},
			$wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM %i WHERE content_id = %d AND content_type = %s ORDER BY id", $this->tablename, $content_id, $content_type )
			)
		);
	}

	/**
	 *
	 * @return HistoryItem[]
	 */
	public function getHistoryOf( string $content_type, int $limit = 1000, int $page = 0 ) {
		$wpdb = $this->wpdb;

		return array_map(
			function ($item) {
				return HistoryItem::parse($item);
			},
			$wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM %i WHERE content_type = %s order by id LIMIT %d OFFSET %d",
					$this->tablename,
					$content_type,
					$limit,
					$page * $limit
				)
			)
		);
	}

	/**
	 * @return HistoryItem[]
	 */
	public function getPostHistory($limit = 1000, $page = 0 ) {
		return $this->getHistoryOf( self::CONTENT_TYPE_POST, $limit, $page );
	}

	/**
	 * @return HistoryItem[]
	 */
	public function getTermTaxonomyHistory($limit = 1000, $page = 0 ) {
		return $this->getHistoryOf( self::CONTENT_TYPE_TERM_TAXONOMY, $limit, $page );
	}

	/**
	 * @return string[]
	 */
	public function getContentTypes(){
		$wpdb = $this->wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT content_type FROM %i", $this->tablename ) );
	}

	/**
	 * @param string $content_type
	 *
	 * @return int
	 */
	public function getCount($content_type = ""){
		$wpdb = $this->wpdb;

		if ( empty( $content_type ) ) {
			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM %i", $this->tablename ) ) );
		}

		return intval( $wpdb->get_var(
			$wpdb->prepare( "SELECT count(id) FROM %i WHERE content_type = %s", $this->tablename, $content_type )
		) );
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
	public function createTables() {
		parent::createTables();
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
