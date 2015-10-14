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

use ApacheSolrForTypo3\Solr\Facet\Facet;
use ApacheSolrForTypo3\Solr\Facet\FacetOption;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class RemoveFacetViewHelper
 */
class RemoveFacetViewHelper extends FacetViewHelper {

	/**
	 * Create remove facet link
	 *
	 * @param Facet $facet
	 * @param FacetOption $facetOption
	 * @param string $optionValue
	 * @param int $pageUid
	 * @param bool $returnUrl
	 * @param string $section The anchor to be added to the url
	 * @return string
	 */
	public function render(Facet $facet, FacetOption $facetOption = NULL, $optionValue = NULL, $pageUid = NULL, $returnUrl = FALSE, $section = '') {
		if ($facetOption === NULL) {
			$facetOption = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\Facet\\FacetOption',
				$facet->getName(),
				$optionValue
			);
		}
		$linkBuilder = $this->getLinkBuilder($facet, $facetOption);
		if ($pageUid) {
			$linkBuilder->setLinkTargetPageId($pageUid);
		}
		$uri = $linkBuilder->getRemoveFacetOptionUrl();
		if ($section) {
			$uri .= '#' . $section;
		}
		if (!$returnUrl) {
			$this->tag->addAttribute('href', $uri, FALSE);
			$this->tag->setContent($this->renderChildren());
			$this->tag->forceClosingTag(TRUE);
			return $this->tag->render();
		} else {
			return $uri;
		}
	}
}
