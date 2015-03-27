<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Widget\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use ApacheSolrForTypo3\Solr\Widget\AbstractWidgetController;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class LastSearchesController
 */
class LastSearchesController extends AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $solrConfiguration = array();

	/**
	 * @var DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * Constructor
	 */
	public function __construct() {
		// todo: fetch from ControllerContext
		$this->solrConfiguration = \Tx_Solr_Util::getSolrConfiguration();
		$this->databaseConnection = $GLOBALS['DB'];
	}

	/**
	 * Last searches
	 */
	public function indexAction() {
		$this->view->assign('contentArguments', array('lastSearches' => $this->getLastSearches()));
	}

	/**
	 * Prepares the content for the last search markers
	 *
	 * @return	array	An array with content for the last search markers
	 */
	protected function getLastSearches() {

		$lastSearches = array();
		$limit = $this->solrConfiguration['search.']['lastSearches.']['limit'];
		switch ($this->solrConfiguration['search.']['lastSearches.']['mode']) {
			case 'user':
				$lastSearches = $this->getLastSearchesFromSession($limit);
				break;
			case 'global':
				$lastSearches = $this->getLastSearchesFromDatabase($limit);
				break;
		}

		return $lastSearches;
	}

	/**
	 * Gets the last searched keywords from the user's session
	 *
	 * @param int $limit
	 * @return array An array containing the last searches of the current user
	 */
	protected function getLastSearchesFromSession($limit = 0) {
		$lastSearches = $GLOBALS['TSFE']->fe_user->getKey(
			'ses',
			'tx_solr_lastSearches'
		);

		if (!is_array($lastSearches)) {
			$lastSearches = array();
		}

		$lastSearches = array_reverse(array_unique($lastSearches));

		if ($limit) {
			$lastSearches = array_slice($lastSearches, 0, (int)$limit);
		}

		return $lastSearches;
	}

	/**
	 * Gets the last searched keywords from the database
	 *
	 * @param int $limit
	 * @return array An array containing the last searches of the current user
	 */
	protected function getLastSearchesFromDatabase($limit = 0) {
		$limit = $limit ? intval($limit) : FALSE;
		$lastSearchesRows = $this->databaseConnection->exec_SELECTgetRows(
			'DISTINCT keywords',
			'tx_solr_last_searches',
			'',
			'',
			'tstamp DESC',
			$limit
		);

		$lastSearches = array();
		foreach ($lastSearchesRows as $row) {
			$lastSearches[] = $row['keywords'];
		}

		return $lastSearches;
	}
}