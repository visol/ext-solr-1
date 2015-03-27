<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Facet;

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
use ApacheSolrForTypo3\Solr\Facet\FacetFluidRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class RenderViewHelper
 */
class RenderViewHelper extends AbstractViewHelper {

	/**
	 * Render facet
	 *
	 * @param \Tx_Solr_Facet_Facet $facet
	 * @return string
	 */
	public function render(\Tx_Solr_Facet_Facet $facet) {
		// todo: fetch from ControllerContext
		$configuration = \Tx_Solr_Util::getSolrConfiguration();
		$configuredFacets = $configuration['search.']['faceting.']['facets.'];
		/** @var \Tx_Solr_Facet_FacetRendererFactory $facetRendererFactory */
		$facetRendererFactory = GeneralUtility::makeInstance(
			'Tx_Solr_Facet_FacetRendererFactory',
			$configuredFacets
		);


		/** @var \Tx_Solr_FacetRenderer $renderer */
		$facetRenderer = $facetRendererFactory->getFacetRendererByFacet($facet);
		if (!$facetRenderer instanceof FacetFluidRendererInterface) {
			/** @var \Tx_Solr_Template $template */
			$template = GeneralUtility::makeInstance(
				'Tx_Solr_Template',
				GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'),
				$configuration['templateFiles.']['results'],
				'single_facet'
			);
			$renderer->setTemplate($template);
			$facetRenderer->setLinkTargetPageId($configuration['search.']['targetPage']);
			$facet = $facetRenderer->getFacetProperties();
			$template->addVariable('facet', $facet);
		}

		return $facetRenderer->renderFacet();
	}
}