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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ResultPaginateController
 */
class ResultPaginateController extends AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array('insertAbove' => TRUE, 'insertBelow' => TRUE, 'maximumNumberOfLinks' => 10, 'addQueryStringMethod' => '');

	/**
	 * @var \Tx_Solr_Search
	 */
	protected $search;

	/**
	 * @var int
	 */
	protected $currentPage = 1;

	/**
	 * @var int
	 */
	protected $displayRangeStart;

	/**
	 * @var int
	 */
	protected $displayRangeEnd;

	/**
	 * @var int
	 */
	protected $maximumNumberOfLinks = 99;

	/**
	 * @var int
	 */
	protected $numberOfPages = 1;

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->search = $this->widgetConfiguration['search'];
		ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $this->widgetConfiguration['configuration'], FALSE);
		$this->configuration['itemsPerPage'] = (int)$this->search->getQuery()->getResultsPerPage();
		$this->numberOfPages = ceil($this->search->getNumberOfResults() / $this->configuration['itemsPerPage']);
		$this->maximumNumberOfLinks = (int)$this->configuration['maximumNumberOfLinks'];
	}

	/**
	 * @param int $page
	 * @return void
	 */
	public function indexAction($page = 1) {
		// set current page
		$this->currentPage = $page;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		}
		$this->view->assign('contentArguments', array(
			$this->widgetConfiguration['as'] => $this->search->getResultDocuments(),
			'pagination' => $this->buildPagination()
		));
		$this->view->assign('configuration', $this->configuration);
	}

	/**
	 * If a certain number of links should be displayed, adjust before and after
	 * amounts accordingly.
	 *
	 * @return void
	 */
	protected function calculateDisplayRange() {
		$maximumNumberOfLinks = $this->maximumNumberOfLinks;
		if ($maximumNumberOfLinks > $this->numberOfPages) {
			$maximumNumberOfLinks = $this->numberOfPages;
		}
		$delta = floor($maximumNumberOfLinks / 2);
		$this->displayRangeStart = $this->currentPage - $delta;
		$this->displayRangeEnd = $this->currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);
		if ($this->displayRangeStart < 1) {
			$this->displayRangeEnd -= $this->displayRangeStart - 1;
		}
		if ($this->displayRangeEnd > $this->numberOfPages) {
			$this->displayRangeStart -= $this->displayRangeEnd - $this->numberOfPages;
		}
		$this->displayRangeStart = (int)max($this->displayRangeStart, 1);
		$this->displayRangeEnd = (int)min($this->displayRangeEnd, $this->numberOfPages);
	}

	/**
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$this->calculateDisplayRange();
		$pages = array();
		for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => $i === $this->currentPage);
		}
		$pagination = array(
			'pages' => $pages,
			'current' => $this->currentPage,
			'numberOfPages' => $this->numberOfPages,
			'displayRangeStart' => $this->displayRangeStart,
			'displayRangeEnd' => $this->displayRangeEnd,
			'hasLessPages' => $this->displayRangeStart > 2,
			'hasMorePages' => $this->displayRangeEnd + 1 < $this->numberOfPages
		);
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		return $pagination;
	}
}