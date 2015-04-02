<?php
namespace ApacheSolrForTypo3\Solr\Controller;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Search Controller
 */
class SearchController extends BaseController {

	/**
	 * Additional filters, which will be added to the query,
	 * as well as to suggest queries
	 *
	 * @var array
	 */
	protected $additionalFilters = array();

	/**
	 * Track, if the number of results per page has been changed
	 * by the current request
	 *
	 * @var boolean
	 */
	protected $resultsPerPageChanged = FALSE;

	/**
	 * Initialize Form action
	 */
	protected function initializeFormAction() {
		$this->initializeAdditionalFilters();
	}

	/**
	 * Initialize results action
	 */
	protected function initializeResultsAction() {
		$this->initializeAdditionalFilters();
		$rawUserQuery = $this->getRawUserQuery();

		// TODO check whether a search has been conducted already?
		if ($this->solrAvailable && (
				isset($rawUserQuery)
				|| $this->configuration['search.']['initializeWithEmptyQuery']
				|| $this->configuration['search.']['initializeWithQuery']
			)) {

			if ($this->configuration['logging.']['query.']['searchWords']) {
				GeneralUtility::devLog('received search query', 'solr', 0, array($rawUserQuery));
			}

			/* @var	$query \Tx_Solr_Query */
			$query = GeneralUtility::makeInstance('Tx_Solr_Query', $rawUserQuery);

			$resultsPerPage = $this->getNumberOfResultsPerPage();
			$query->setResultsPerPage($resultsPerPage);

			$searchComponents = GeneralUtility::makeInstance('Tx_Solr_Search_SearchComponentManager')->getSearchComponents();
			foreach ($searchComponents as $searchComponent) {
				$searchComponent->setSearchConfiguration($this->configuration['search.']);

				if ($searchComponent instanceof \Tx_Solr_QueryAware) {
					$searchComponent->setQuery($query);
				}
// todo: check for replacement
//				if ($searchComponent instanceof \Tx_Solr_PluginAware) {
//					$searchComponent->setParentPlugin($this);
//				}

				$searchComponent->initializeSearchComponent();
			}

			if ($this->configuration['search.']['initializeWithEmptyQuery'] || $this->configuration['search.']['query.']['allowEmptyQuery']) {
				// empty main query, but using a "return everything"
				// alternative query in q.alt
				$query->setAlternativeQuery('*:*');
			}

			if ($this->configuration['search.']['initializeWithQuery']) {;
				$query->setAlternativeQuery($this->configuration['search.']['initializeWithQuery']);
			}

			foreach($this->additionalFilters as $additionalFilter) {
				$query->addFilter($additionalFilter);
			}

			$this->query = $query;
		}
	}


	/**
	 * Initializes additional filters configured through TypoScript and
	 * Flexforms for use in regular queries and suggest queries.
	 *
	 * @return void
	 */
	protected function initializeAdditionalFilters() {
		$additionalFilters = array();

		if(!empty($this->configuration['search.']['query.']['filter.'])) {
			// special filter to limit search to specific page tree branches
			if (array_key_exists('__pageSections', $this->configuration['search.']['query.']['filter.'])) {
				$this->query->setRootlineFilter($this->configuration['search.']['query.']['filter.']['__pageSections']);
				unset($this->configuration['search.']['query.']['filter.']['__pageSections']);
			}

			// all other regular filters
			foreach($this->configuration['search.']['query.']['filter.'] as $filterKey => $filter) {
				if (!is_array($this->configuration['search.']['query.']['filter.'][$filterKey])) {
					if (is_array($this->configuration['search.']['query.']['filter.'][$filterKey . '.'])) {
						$filter = $this->contentObjectRenderer->stdWrap(
							$this->configuration['search.']['query.']['filter.'][$filterKey],
							$this->configuration['search.']['query.']['filter.'][$filterKey . '.']
						);
					}

					$additionalFilters[$filterKey] = $filter;
				}
			}
		}

		// todo: check if needed
		// flexform overwrites _all_ filters set through TypoScript
//		$flexformFilters = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'filter', 'sQuery');
//		if (!empty($flexformFilters)) {
//			$additionalFilters = t3lib_div::trimExplode('|', $flexformFilters);
//		}

		$this->additionalFilters = $additionalFilters;
	}

	/**
	 * Returns the number of results per Page.
	 *
	 * Also influences how many result documents are returned by the Solr
	 * server as the return value is used in the Solr "rows" GET parameter.
	 *
	 * @return	int	number of results to show per page
	 */
	public function getNumberOfResultsPerPage() {
		$configuration = \Tx_Solr_Util::getSolrConfiguration();
		$resultsPerPageSwitchOptions = GeneralUtility::intExplode(',', $configuration['search.']['results.']['resultsPerPageSwitchOptions']);

		$solrParameters = $this->request->getArguments();

		if (isset($solrParameters['resultsPerPage']) && in_array($solrParameters['resultsPerPage'], $resultsPerPageSwitchOptions)) {
			$this->typoScriptFrontendController->fe_user->setKey('ses', 'tx_solr_resultsPerPage', intval($solrParameters['resultsPerPage']));
			$this->resultsPerPageChanged = TRUE;
		}

		$defaultNumberOfResultsShown = $this->configuration['search.']['results.']['resultsPerPage'];
		$userSetNumberOfResultsShown = $this->typoScriptFrontendController->fe_user->getKey('ses', 'tx_solr_resultsPerPage');

		$currentNumberOfResultsShown = $defaultNumberOfResultsShown;
		if (!is_null($userSetNumberOfResultsShown) && in_array($userSetNumberOfResultsShown, $resultsPerPageSwitchOptions)) {
			$currentNumberOfResultsShown = (int) $userSetNumberOfResultsShown;
		}

		$rawUserQuery = $this->getRawUserQuery();

		if (($this->configuration['search.']['initializeWithEmptyQuery'] || $this->configuration['search.']['initializeWithQuery'])
			&& !$this->configuration['search.']['showResultsOfInitialEmptyQuery']
			&& !$this->configuration['search.']['showResultsOfInitialQuery']
			&& empty($rawUserQuery)
		) {
			// initialize search with an empty query, which would by default return all documents
			// anyway, tell Solr to not return any result documents
			// Solr will still return facets though
			$currentNumberOfResultsShown = 0;
		}

		return $currentNumberOfResultsShown;
	}


	/**
	 * Executes the actual search.
	 *
	 */
	protected function search() {
		if (!is_null($this->query)
			&& ($this->query->getQueryString()
				|| $this->configuration['search.']['initializeWithEmptyQuery']
				|| $this->configuration['search.']['showResultsOfInitialEmptyQuery']
				|| $this->configuration['search.']['initializeWithQuery']
				|| $this->configuration['search.']['showResultsOfInitialQuery']
			)) {
			$currentPage = 1;
			// The pagination
			if ($this->request->hasArgument('page')) {
				$currentPage = max(1, (int)$this->request->getArgument('page'));
			}

			// if the number of results per page has been changed by the current request, reset the pagebrowser
			if($this->resultsPerPageChanged) {
				$currentPage = 1;
			}

			$offSet = ($currentPage-1) * $this->query->getResultsPerPage();

			// performing the actual search, sending the query to the Solr server
			$this->search->search($this->query, $offSet, NULL);
			$response = $this->search->getResponse();

			$this->processResponse($this->query, $response);
		}
	}

	/**
	 * Provides a hook for other classes to process the search's response.
	 *
	 * @param	Tx_Solr_Query	The query that has been searched for.
	 * @param	Apache_Solr_Response	The search's reponse.
	 */
	protected function processResponse(\Tx_Solr_Query $query, \Apache_Solr_Response $response) {
		$rawUserQuery = $this->getRawUserQuery();

		if (($this->configuration['search.']['initializeWithEmptyQuery'] || $this->configuration['search.']['initializeWithQuery'])
			&& !$this->configuration['search.']['showResultsOfInitialEmptyQuery']
			&& !$this->configuration['search.']['showResultsOfInitialQuery']
			&& empty($rawUserQuery)
		) {
			// explicitly set number of results to 0 as we just wanted
			// facets and the like according to configuration
			// @see	getNumberOfResultsPerPage()
			$response->response->numFound = 0;
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['processSearchResponse'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['processSearchResponse'] as $classReference) {
				$responseProcessor = GeneralUtility::getUserObj($classReference);

				if ($responseProcessor instanceof \Tx_Solr_ResponseProcessor) {
					$responseProcessor->processResponse($query, $response);
				}
			}
		}
	}

	/**
	 * Results
	 */
	public function resultsAction() {
		$this->search();
		$this->view->assignMultiple(array(
			'search' => $this->search,
			'additionalFilters' => $this->additionalFilters,
		));
	}

	/**
	 * Form
	 */
	public function formAction() {
		$this->view->assignMultiple(array(
			'search' => $this->search,
			'additionalFilters' => $this->additionalFilters,
		));
	}

	/**
	 * Frequently Searched
	 */
	public function frequentlySearchedAction() {

	}
}