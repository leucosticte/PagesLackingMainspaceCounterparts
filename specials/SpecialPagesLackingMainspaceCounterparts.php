<?php

class SpecialPagesLackingMainspaceCounterparts extends PageQueryPage {
	public function __construct() {
		parent::__construct( 'PagesLackingMainspaceCounterparts' );
	}

	/**
	 * The content returned by this function is output before any result.
	 *
	 * @return string
	 */
	public function getPageHeader() {
		return $this->msg( 'pageslackingmainspacecounterparts-text' )->parseAsBlock();
	}

	/**
	 * Overridden to prevent sorting by increasing values.
	 *
	 * @return bool
	 */
	public function sortDescending() {
		return false;
	}

	/**
	 * Is this query expensive? Then we
	 * don't let it run in miser mode. $wgDisableQueryPages causes all query
	 * pages to be declared expensive. Some query pages are always expensive.
	 *
	 * @return bool
	 */
	public function isExpensive() {
		return true;
	}

	/**
	 * Sometime we don't want to build rss / atom feeds.
	 *
	 * @return bool
	 */
	public function isSyndicated() {
		return false;
	}

	/**
	 * Subclasses return an SQL query here, formatted as an array with the
	 * following keys:
	 *    tables => Table(s) for passing to Database::select()
	 *    fields => Field(s) for passing to Database::select(), may be *
	 *    conds => WHERE conditions
	 *    options => options
	 *    join_conds => JOIN conditions
	 *
	 * Note that the query itself should return the following three columns:
	 * 'namespace', 'title', and 'value'. 'value' is used for sorting.
	 *
	 * These may be stored in the querycache table for expensive queries,
	 * and that cached data will be returned sometimes, so the presence of
	 * extra fields can't be relied upon. The cached 'value' column will be
	 * an integer; non-numeric values are useful only for sorting the
	 * initial query (except if they're timestamps, see usesTimestamps()).
	 *
	 * Don't include an ORDER or LIMIT clause, they will be added.
	 *
	 * If this function is not overridden or returns something other than
	 * an array, getSQL() will be used instead. This is for backwards
	 * compatibility only and is strongly deprecated.
	 * @return array
	 */
	public function getQueryInfo() : array {
		global $wgPagesLackingMainspaceCounterpartsNamespaces;

		$namespaces = [];

		// Check if the configuration global is an integer, so single values still work
		if ( is_int( $wgPagesLackingMainspaceCounterpartsNamespaces ) ) {
			$namespaces[] = $wgPagesLackingMainspaceCounterpartsNamespaces;
		} elseif ( is_array( $wgPagesLackingMainspaceCounterpartsNamespaces ) ) {
			$namespaces = $wgPagesLackingMainspaceCounterpartsNamespaces;
		}

		$query = [
			'tables' => 'page AS p1',
			'fields' => [
				'namespace' => 'p1.page_namespace',
				'title' => 'p1.page_title',
				// Sorting
				'value' => 'page_title'
			],
			'conds' => [
				'p1.page_title NOT LIKE "%/%"' # Not sure what this does
			]
		];

		// Loop through the exempted namespaces
		$nsCond = "p1.page_namespace = 3000";
		$first = true;
		foreach ( $namespaces as $namespace ) {
			// Skip through non-integer values
			if ( !is_int( $namespace ) ) {
				continue;
			}
			if ( $first ) {
				$first = false;
				$nsCond = '';
			} else {
				$nsCond .= ' OR ';
			}
			$nsCond .= "p1.page_namespace = $namespace";
		}
		$query['conds'][] = $nsCond;

		// Add the final condition
		$query['conds'][] = 'NOT EXISTS (SELECT 1 FROM page AS p2 WHERE p2.page_namespace = 0 AND p1.page_title = p2.page_title)';

		return $query;
	}

	/**
	 * Under which header this special page is listed in Special:SpecialPages.
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'maintenance';
	}
}
