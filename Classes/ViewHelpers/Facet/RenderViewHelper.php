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

use ApacheSolrForTypo3\Solr\Facet\Facet;
use ApacheSolrForTypo3\Solr\Facet\FacetFluidRendererInterface;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * Class RenderViewHelper
 */
class RenderViewHelper extends AbstractViewHelper {

	/**
	 * Render facet
	 *
	 * @param Facet $facet
	 * @return string
	 */
	public function render(Facet $facet) {
		// todo: fetch from ControllerContext
		$configuration = Util::getSolrConfiguration();
		$configuredFacets = $configuration['search.']['faceting.']['facets.'];
		$facetRendererFactory = GeneralUtility::makeInstance(
			'ApacheSolrForTypo3\\Solr\\Facet\\FacetRendererFactory',
			$configuredFacets
		);

		$facetRenderer = $facetRendererFactory->getFacetRendererByFacet($facet);
		if (!$facetRenderer instanceof FacetFluidRendererInterface) {
			$template = GeneralUtility::makeInstance(
				'ApacheSolrForTypo3\\Solr\\Template',
				GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'),
				$configuration['templateFiles.']['results'],
				'single_facet'
			);
			$facetRenderer->setTemplate($template);
			$facetRenderer->setLinkTargetPageId($configuration['search.']['targetPage']);
			$facet = $facetRenderer->getFacetProperties();
			$template->addVariable('facet', $facet);
		}

		return $facetRenderer->renderFacet();
	}
}
