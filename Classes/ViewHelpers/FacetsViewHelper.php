<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers;

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

use ApacheSolrForTypo3\Solr\Facet\FacetRendererFactory;
use ApacheSolrForTypo3\Solr\Search;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * Facets ViewHelper
 */
class FacetsViewHelper extends AbstractViewHelper {

	/**
	 * @var Search
	 */
	protected $search;

	/**
	 * @var FacetRendererFactory
	 */
	protected $facetRendererFactory;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var bool
	 */
	protected $facetsActive = FALSE;

	/**
	 * Constructor
	 */
	public function __construct() {
		// todo: fetch from ControllerContext
		$this->configuration = Util::getSolrConfiguration();
	}

	/**
	 * Get facets
	 *
	 * @param Search $search
	 * @param string $facets variable name for the facets
	 * @param string $usedFacets variable name for usedFacets
	 * @return string
	 */
	public function render(Search $search, $facets = 'facets', $usedFacets = 'usedFacets') {
		$this->search = $search;
		$this->facetRendererFactory = GeneralUtility::makeInstance(
			'ApacheSolrForTypo3\\Solr\\Facet\\FacetRendererFactory',
			$this->configuration['search.']['faceting.']['facets.']
		);

		$templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();
		$templateVariableContainer->add($facets, $this->getAvailableFacets());
		$templateVariableContainer->add($usedFacets, $this->getUsedFacets());

		$content = $this->renderChildren();

		$templateVariableContainer->remove($facets);
		$templateVariableContainer->remove($usedFacets);

		return $content;
	}

	/**
	 * Get available facet objects
	 *
	 * @return \ApacheSolrForTypo3\Solr\Facet\Facet[]
	 */
	protected function getAvailableFacets() {

		$facets = array();
		$configuredFacets = $this->configuration['search.']['faceting.']['facets.'];
		foreach ($configuredFacets as $facetName => $facetConfiguration) {
			$facetName = substr($facetName, 0, -1);
			$facet = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\Facet\\Facet',
				$facetName,
				$this->facetRendererFactory->getFacetInternalType($facetName)
			);

			if (
				(isset($facetConfiguration['includeInAvailableFacets']) && $facetConfiguration['includeInAvailableFacets'] == '0')
				|| !$facet->isRenderingAllowed()
			) {
				// don't render facets that should not be included in available facets
				// or that do not meet their requirements to be rendered
				continue;
			}

			if ($facet->isActive()) {
				$this->facetsActive = TRUE;
			}
			$facets[] = $facet;
		}

		return $facets;
	}

	/**
	 *
	 */
	protected function getUsedFacets() {

		$resultParameters = GeneralUtility::_GET('tx_solr');
		$filterParameters = array();
		if (isset($resultParameters['filter'])) {
			$filterParameters = (array) array_map('urldecode', $resultParameters['filter']);
		}

		$facetsInUse = array();
		foreach ($filterParameters as $filter) {
			// only split by the first ":" to allow the use of colons in the filter value
			list($facetName, $filterValue) = explode(':', $filter, 2);

			$facetConfiguration = $this->configuration['search.']['faceting.']['facets.'][$facetName . '.'];

			// don't render facets that should not be included in used facets
			if (empty($facetConfiguration)
				||
				(isset($facetConfiguration['includeInUsedFacets']) && $facetConfiguration['includeInUsedFacets'] == '0')
			) {
				continue;
			}

			/** @var \ApacheSolrForTypo3\Solr\Facet\Facet $facet */
			$facet = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\Facet\\Facet',
				$facetName,
				$this->facetRendererFactory->getFacetInternalType($facetName)
			);

			$facetsInUse[] = $facet;
		}

		return $facetsInUse;
	}
}
