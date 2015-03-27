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
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class FrequentlySearchedController
 */
class FrequentlySearchedController extends AbstractWidgetController {

	/**
	 * Instance of the caching frontend used to cache this command's output.
	 *
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cacheInstance;

	/**
	 * @var DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * @var array
	 */
	protected $solrConfiguration = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// todo: fetch from ControllerContext
		$this->solrConfiguration = \Tx_Solr_Util::getSolrConfiguration();
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
		$this->initializeCache();
	}

	/**
	 * Initializes the cache for this command.
	 *
	 * @return void
	 */
	protected function initializeCache() {
		$cacheIdentifier = 'tx_solr';
		try {
			$this->cacheInstance = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache($cacheIdentifier);
		} catch (NoSuchCacheException $e) {
			/** @var t3lib_cache_Factory $typo3CacheFactory */
			$typo3CacheFactory = $GLOBALS['typo3CacheFactory'];
			$this->cacheInstance = $typo3CacheFactory->create(
				$cacheIdentifier,
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['options']
			);
		}
	}

	/**
	 * Last searches
	 */
	public function indexAction() {
		$frequentSearches = $this->getFrequentSearchTerms();
		$this->view->assign('contentArguments', array('frequentSearches' => $this->enrichFrequentSearchesInfo($frequentSearches)));
	}

	/**
	 * Enrich the frequentSearches
	 *
	 * @param array Frequent search terms as array with terms as keys and hits as the value
	 * @return array An array with content for the frequent terms markers
	 */
	protected function enrichFrequentSearchesInfo(array $frequentSearchTerms) {
		$frequentSearches = array();

		$minimumSize = $this->solrConfiguration['search.']['frequentSearches.']['minSize'];
		$maximumSize = $this->solrConfiguration['search.']['frequentSearches.']['maxSize'];

		if (count($frequentSearchTerms)) {
			$maximumHits = max(array_values($frequentSearchTerms));
			$minimumHits = min(array_values($frequentSearchTerms));
			$spread = $maximumHits - $minimumHits;
			$step = ($spread == 0) ? 1 : ($maximumSize - $minimumSize) / $spread;

			foreach ($frequentSearchTerms as $term => $hits) {
				$size = round($minimumSize + (($hits - $minimumHits) * $step));
				$frequentSearches[] = array(
					'q' => $term,
					'hits' => $hits,
					'style' => 'font-size: ' . $size . 'px',
					'class' => 'tx-solr-frequent-term-' . $size,
					'size' => $size,
				);
			}

		}

		return $frequentSearches;
	}

	/**
	 * Generates an array with terms and hits
	 *
	 * @return Tags as array with terms and hits
	 */
	protected function getFrequentSearchTerms() {
		$terms = array();

		// Use configuration as cache identifier
		$identifier = 'frequentSearchesTags_' . md5(serialize($this->solrConfiguration['search.']['frequentSearches.']));

		if ($this->cacheInstance->has($identifier)) {
			$terms = $this->cacheInstance->get($identifier);
		} else {
			$terms = $this->getFrequentSearchTermsFromStatistics();

			if($this->solrConfiguration['search.']['frequentSearches.']['sortBy'] == 'hits') {
				arsort($terms);
			} else {
				ksort($terms);
			}

			$lifetime = NULL;
			if (isset($this->solrConfiguration['search.']['frequentSearches.']['cacheLifetime'])) {
				$lifetime = intval($this->solrConfiguration['search.']['frequentSearches.']['cacheLifetime']);
			}

			$this->cacheInstance->set($identifier, $terms, array(), $lifetime);
		}

		return $terms;
	}

	/**
	 * Gets frequent search terms from the statistics tracking table.
	 *
	 * @return array Array of frequent search terms, keys are the terms, values are hits
	 */
	protected function getFrequentSearchTermsFromStatistics() {
		$terms = array();

		if ($this->solrConfiguration['search.']['frequentSearches.']['select.']['checkRootPageId']) {
			$checkRootPidWhere = 'root_pid = ' . $GLOBALS['TSFE']->tmpl->rootLine[0]['uid'];
		} else {
			$checkRootPidWhere = '1';
		}
		if ($this->solrConfiguration['search.']['frequentSearches.']['select.']['checkLanguage']) {
			$checkLanguageWhere = ' AND language =' . $GLOBALS['TSFE']->sys_language_uid;
		} else {
			$checkLanguageWhere = '';
		}

		$sql = $this->solrConfiguration['search.']['frequentSearches.'];
		$sql['select.']['ADD_WHERE'] = $checkRootPidWhere . $checkLanguageWhere . ' ' . $sql['select.']['ADD_WHERE'];

		$frequentSearchTerms = $this->databaseConnection->exec_SELECTgetRows(
			$sql['select.']['SELECT'],
			$sql['select.']['FROM'],
			$sql['select.']['ADD_WHERE'],
			$sql['select.']['GROUP_BY'],
			$sql['select.']['ORDER_BY'],
			$sql['limit']
		);

		foreach ($frequentSearchTerms as $term) {
			$terms[$term['search_term']] = $term['hits'];
		}

		return $terms;
	}
}