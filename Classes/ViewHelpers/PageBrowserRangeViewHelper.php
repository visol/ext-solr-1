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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class PageBrowserRangeViewHelper
 */
class PageBrowserRangeViewHelper extends AbstractViewHelper {

	/**
	 * @param \Tx_Solr_Search $search
	 * @param string $from variable name for from value
	 * @param string $to variable name for to value
	 * @param string $total variable name for total value
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function render(\Tx_Solr_Search $search, $from = 'from', $to = 'to', $total = 'total') {

		$templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();

		$resultsFrom = $search->getResponseBody()->start + 1;
		$resultsTo = $resultsFrom + count($search->getResultDocuments()) - 1;
		$templateVariableContainer->add($from, $resultsFrom);
		$templateVariableContainer->add($to, $resultsTo);
		$templateVariableContainer->add($total, $search->getNumberOfResults());

		$content = $this->renderChildren();

		$templateVariableContainer->remove($from);
		$templateVariableContainer->remove($to);
		$templateVariableContainer->remove($total);

		return $content;
	}
}