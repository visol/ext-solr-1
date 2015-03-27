<?php
namespace ApacheSolrForTypo3\Solr\Facet;

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
 * Fluid based QueryGroup FacetRenderer
 */
class QueryGroupFacetFluidRenderer extends SimpleFacetFluidRenderer {

	/**
	 * Provides the internal type of facets the renderer handles.
	 * The type is one of field, range, or query.
	 *
	 * @return string Facet internal type
	 */
	public static function getFacetInternalType() {
		return \Tx_Solr_Facet_Facet::TYPE_QUERY;
	}

	/**
	 * Encodes the facet option values from raw Lucene queries to values that
	 * can be easily used in rendering instructions and URL generation.
	 *
	 * @see Tx_Solr_Facet_AbstractFacetRenderer::getFacetOptions()
	 * @return array
	 */
	public function getFacetOptions() {
		$facetOptions = array();
		$facetOptionsRaw = parent::getFacetOptions();

		$filterEncoder = GeneralUtility::makeInstance('Tx_Solr_Query_FilterEncoder_QueryGroup');
		foreach ($facetOptionsRaw as $facetOption => $numberOfResults) {
			$facetOption = $filterEncoder->encodeFilter($facetOption, $this->facetConfiguration);
			$facetOptions[$facetOption] = $numberOfResults;
		}

		return $facetOptions;
	}
}