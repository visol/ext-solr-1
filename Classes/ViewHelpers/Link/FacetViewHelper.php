<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Link;

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

/**
 * Class FacetViewHelper
 */
class FacetViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper {

	/**
	 * @var \Tx_Solr_Search
	 */
	protected $search;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search');
	}

	/**
	 * Create add facet link
	 *
	 * @param \Tx_Solr_Facet_Facet $facet
	 * @param \Tx_Solr_Facet_FacetOption $facetOption
	 * @param int $pageUid
	 * @param bool $returnUrl
	 * @return string
	 */
	public function render(\Tx_Solr_Facet_Facet $facet, \Tx_Solr_Facet_FacetOption $facetOption, $pageUid = NULL, $returnUrl = FALSE) {
		$linkBuilder = $this->getLinkBuilder($facet, $facetOption);
		if ($pageUid) {
			$linkBuilder->setLinkTargetPageId($pageUid);
		}
		$uri = $linkBuilder->getAddFacetOptionUrl();
		if (!$returnUrl) {
			$this->tag->addAttribute('href', $uri, FALSE);
			$this->tag->setContent($this->renderChildren());
			$this->tag->forceClosingTag(TRUE);
			return $this->tag->render();
		} else {
			return $uri;
		}
	}

	/**
	 * Get LinkBuilder
	 *
	 * @param \Tx_Solr_Facet_Facet $facet
	 * @param \Tx_Solr_Facet_FacetOption $facetOption
	 * @return \Tx_Solr_Facet_LinkBuilder
	 */
	protected function getLinkBuilder(\Tx_Solr_Facet_Facet $facet, \Tx_Solr_Facet_FacetOption $facetOption) {
		/** @var \Tx_Solr_Facet_LinkBuilder $linkBuilder */
		$linkBuilder = GeneralUtility::makeInstance(
			'Tx_Solr_Facet_LinkBuilder',
			$this->search->getQuery(),
			$facet->getName(),
			$facetOption
		);
		return $linkBuilder;
	}
}