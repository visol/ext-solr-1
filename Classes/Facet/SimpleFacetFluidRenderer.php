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
 * Class FluidRenderer
 */
class SimpleFacetFluidRenderer extends AbstractFacetFluidRenderer {

	/**
	 * Provides the internal type of facets the renderer handles.
	 * The type is one of field, range, or query.
	 *
	 * @return string Facet internal type
	 */
	public static function getFacetInternalType() {
		return \Tx_Solr_Facet_Facet::TYPE_FIELD;
	}

	/**
	 * Renders the facet's options.
	 *
	 * @return string The rendered facet options.
	 */
	protected function renderFacetOptions() {

		$rawFacetOptions = $this->getFacetOptions();
		$facetOptions = array();

		if (!empty($this->facetConfiguration['manualSortOrder'])) {
			$rawFacetOptions = $this->sortFacetOptionsByUserDefinedOrder($rawFacetOptions);
		}

		if (!empty($this->facetConfiguration['reverseOrder'])) {
			$rawFacetOptions = array_reverse($rawFacetOptions, true);
		}

		foreach ($rawFacetOptions as $facetOption => $facetOptionResultCount) {
			$facetOption = (string) $facetOption;
			if ($facetOption === '_empty_') {
				// skip missing facets
				continue;
			}

			/* @var $facetOption \Tx_Solr_Facet_FacetOption */
			$facetOption = GeneralUtility::makeInstance('Tx_Solr_Facet_FacetOption',
				$this->facetName,
				$facetOption,
				$facetOptionResultCount
			);

			$optionText = $facetOption->render();
			$optionSelected = $facetOption->isSelectedInFacet($this->facetName);

			$facetOptions[] = array(
				'label' => $optionText,
				'value' => $facetOption->getValue(),
				'count' => $facetOption->getNumberOfResults(),
				'selected' => (bool)$optionSelected,
				'facetName' => $this->facetName,
				'facetOption' => $facetOption
			);
		}

		$this->view->assign('options', $facetOptions);
	}

	/**
	 * Sorts the facet options as defined in the facet's manualSortOrder
	 * configuration option.
	 *
	 * @return array
	 */
	protected function sortFacetOptionsByUserDefinedOrder($facetOptions) {
		$sortedOptions = array();

		$manualFacetOptionSortOrder = GeneralUtility::trimExplode(',', $this->facetConfiguration['manualSortOrder']);
		$availableFacetOptions = array_keys($facetOptions);

		// move the configured options to the top, in their defined order
		foreach ($manualFacetOptionSortOrder as $manuallySortedFacetOption) {
			if (in_array($manuallySortedFacetOption, $availableFacetOptions)) {
				$sortedOptions[$manuallySortedFacetOption] = $facetOptions[$manuallySortedFacetOption];
				unset($facetOptions[$manuallySortedFacetOption]);
			}
		}

		// set the facet options to the new order,
		// appending the remaining unsorted/regularly sorted options
		return $sortedOptions + $facetOptions;
	}

}