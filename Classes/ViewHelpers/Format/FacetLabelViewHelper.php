<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Format;

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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class facetOptionLabelViewHelper
 */
class FacetLabelViewHelper extends AbstractViewHelper implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var ContentObjectRenderer
	 */
	protected $contentObjectRenderer;

	/**
	 * Constructor
	 */
	public function __construct() {
		// todo: fetch from ControllerContext
		$this->configuration = \Tx_Solr_Util::getSolrConfiguration();
		$this->contentObjectRenderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
	}

	/**
	 * @param \Tx_Solr_Facet_Facet $facet
	 * @return string
	 */
	public function render(\Tx_Solr_Facet_Facet $facet) {
		$facetLabel = $this->contentObjectRenderer->stdWrap(
			$this->configuration['search.']['faceting.']['facets.'][$facet->getName() . '.']['label'],
			$this->configuration['search.']['faceting.']['facets.'][$facet->getName() . '.']['label.']
		);

		return $facetLabel ?: $facet->getName();
	}

}