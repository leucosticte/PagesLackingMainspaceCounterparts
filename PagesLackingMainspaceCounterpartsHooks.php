<?php

class PagesLackingMainspaceCounterparts {
	/**
	 * Hook to add Special:PagesLackingMainspaceCounterparts to the list generated by QueryPage::getPages.
	 * Used by the maintenance script updateSpecialPages.
	 *
	 * @param array &$wgQueryPages
	 * @return bool
	 */
	public static function onwgQueryPages( array &$wgQueryPages ) {
		$wgQueryPages[] = [ 'SpecialPagesLackingMainspaceCounterparts', 'PagesLackingMainspaceCounterparts' ];

		return true;
	}
}
