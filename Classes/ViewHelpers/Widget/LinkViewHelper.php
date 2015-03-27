<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Widget;

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

/**
 * Class LinkViewHelper
 */
class LinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Widget\LinkViewHelper {

	/**
	 * Get the URI for a non-AJAX Request.
	 *
	 * @return string the Widget URI
	 */
	protected function getWidgetUri() {
		$uriBuilder = $this->controllerContext->getUriBuilder();
		$argumentPrefix = $this->controllerContext->getRequest()->getArgumentPrefix();
		$arguments = $this->hasArgument('arguments') ? $this->arguments['arguments'] : array();
		if ($this->hasArgument('addQueryStringMethod') && $this->arguments['addQueryStringMethod'] !== '') {
			$arguments['addQueryStringMethod'] = $this->arguments['addQueryStringMethod'];
		}
		return $uriBuilder->reset()->setUseCacheHash(FALSE)->setArguments(array($argumentPrefix => $arguments))->setSection($this->arguments['section'])->setAddQueryString(TRUE)->setAddQueryStringMethod($this->arguments['addQueryStringMethod'])->setArgumentsToBeExcludedFromQueryString(array($argumentPrefix . '[page]', 'cHash'))->setFormat($this->arguments['format'])->build();
	}
}