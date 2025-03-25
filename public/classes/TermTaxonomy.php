<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-04
 * Time: 12:01
 */

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\Component;

class TermTaxonomy extends Component {

	public function onCreate( ):void {
		// TODO: delete history on delete
		add_action( 'edit_terms', array( $this, 'edit_terms' ), 10, 3 );
		add_action( 'get_header', array( $this, 'get_header' ) );
	}

	/**
	 *
	 * @param $term_id
	 * @param $taxonomy
	 */
	public function edit_terms( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );
		if ( ! $term ) {
			return;
		}
		$this->savePermalinkInHistory( $term );
	}

	/**
	 * save on load term page
	 */
	public function get_header() {
		$obj = get_queried_object();
		if ( $obj instanceof \WP_Term ) {
			$this->savePermalinkInHistory( $obj );
		}
	}

	/**
	 * add permalink to history if no exists
	 *
	 * @param \WP_Term $term
	 *
	 * @return bool|false|int
	 */
	public function savePermalinkInHistory( $term ) {

		$permalink_without_domain = $this->getEscapedPermalink( $term );
		if ( $this->plugin->database->termTaxonomyPermalinkHistoryExists($term->term_taxonomy_id, $permalink_without_domain ) ) {
			return false;
		}

		return $this->plugin->database->addTermTaxonomyPermalink( $term->term_taxonomy_id, $permalink_without_domain );
	}

	/**
	 * @param int $term_taxonomy_id
	 *
	 * @return \WP_Term|\WP_Error
	 */
	function getTerm( $term_taxonomy_id ) {
		return get_term_by( 'term_taxonomy_id', $term_taxonomy_id );
	}

	/**
	 * @param \WP_Term|int $term_or_term_taxonomy_id
	 *
	 * @return false | string
	 */
	function getEscapedPermalink( $term_or_term_taxonomy_id ) {
		$term = ( ! ( $term_or_term_taxonomy_id instanceof \WP_Term ) ) ?
			$term_or_term_taxonomy_id = $this->getTerm( $term_or_term_taxonomy_id )
			:
			$term_or_term_taxonomy_id;

		return ($term instanceof \WP_Term)? Utils::getUrlPath( get_term_link( $term ) ) : false;
	}
}
