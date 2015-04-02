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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class SearchFormViewHelper
 */
class SearchFormViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * @var \Tx_Solr_Search $search
	 */
	protected $search;

	/**
	 * @var string
	 */
	protected $tagName = 'form';

	/**
	 * @var TypoScriptFrontendController
	 */
	protected $frontendController;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->frontendController =  $GLOBALS['TSFE'];
		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search');
		// todo: fetch from ControllerContext
		$this->configuration = \Tx_Solr_Util::getSolrConfiguration();
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerTagAttribute('enctype', 'string', 'MIME type with which the form is submitted');
		$this->registerTagAttribute('method', 'string', 'Transfer type (GET or POST)', FALSE, 'get');
		$this->registerTagAttribute('name', 'string', 'Name of form');
		$this->registerTagAttribute('onreset', 'string', 'JavaScript: On reset of the form');
		$this->registerTagAttribute('onsubmit', 'string', 'JavaScript: On submit of the form');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render search form tag
	 *
	 * @param int|NULL $pageUid When not set current page is used
	 * @param array|NULL $additionalFilters Additional filters
	 * @param array $additionalParams query parameters to be attached to the resulting URI
	 * @param integer $pageType type of the target page. See typolink.parameter
	 * @param boolean $noCache set this to disable caching for the target page. You should not need this.
	 * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
	 * @param boolean $absolute If set, the URI of the rendered link is absolute
	 * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
	 * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = TRUE
	 * @param string $addQueryStringMethod Set which parameters will be kept. Only active if $addQueryString = TRUE
	 * @param bool $addSuggestUrl
	 * @return string
	 */
	public function render($pageUid = NULL, $additionalFilters = NULL, array $additionalParams = array(), $noCache = FALSE, $pageType = 0, $noCacheHash = FALSE, $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array(), $addQueryStringMethod = NULL, $addSuggestUrl = TRUE) {

		if ($pageUid === NULL && !empty($this->configuration['search.']['targetPage'])) {
			$pageUid = (int)$this->configuration['search.']['targetPage'];
		}

		$uriBuilder = $this->controllerContext->getUriBuilder();
		$uri = $uriBuilder->reset()
			->setTargetPageUid($pageUid)
			->setTargetPageType($pageType)
			->setNoCache($noCache)
			->setUseCacheHash(!$noCacheHash)
			->setArguments($additionalParams)
			->setCreateAbsoluteUri($absolute)
			->setAddQueryString($addQueryString)
			->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
			->setAddQueryStringMethod($addQueryStringMethod)
			->build();

		$this->tag->addAttribute('action', $uri);
		if ($addSuggestUrl) {
			$this->tag->addAttribute('data-suggest', $this->getSuggestEidUrl($additionalFilters));
		}
		$this->tag->addAttribute('accept-charset', $this->frontendController->metaCharset);

		// Get search term
		$q = '';
		if ($this->search->hasSearched()) {
			$this->search->getQuery()->getKeywordsCleaned();
		} elseif (GeneralUtility::_GET('q')) {
			$q = GeneralUtility::_GET('q');
		}

		// Render form content
		$this->templateVariableContainer->add('q', $q);
		$this->templateVariableContainer->add('pageUid', $this->frontendController->id);
		$this->templateVariableContainer->add('languageUid', $this->frontendController->sys_language_uid);
		$formContent = $this->renderChildren();
		$this->templateVariableContainer->remove('q');
		$this->templateVariableContainer->remove('pageUid');
		$this->templateVariableContainer->remove('languageUid');

		$this->tag->setContent($formContent);

		return $this->tag->render();
	}

	/**
	 * Returns the eID URL for the AJAX suggestion request
	 *
	 * This link should be touched by realurl etc
	 *
	 * @return string the full URL to the eID script including the needed parameters
	 */
	/**
	 * @param NULL|array $additionalFilters
	 * @return string
	 */
	protected function getSuggestEidUrl($additionalFilters) {

		$suggestUrl = $this->frontendController->absRefPrefix;

//		if ($this->configuration['suggest.']['forceHttps']) {
//			$suggestUrl = str_replace('http://', 'https://', $suggestUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
//		}

		$suggestUrl .= '?eID=tx_solr_suggest&id=' . $this->frontendController->id;

		// add filters
		if (!empty($additionalFilters)) {
			$additionalFilters = json_encode($additionalFilters);
			$additionalFilters = rawurlencode($additionalFilters);

			$suggestUrl .= '&filters=' . $additionalFilters;
		}

		// adds the language parameter to the suggest URL
		if ($this->frontendController->sys_language_uid > 0) {
			$suggestUrl .= '&L=' . $this->frontendController->sys_language_uid;
		}

		return $suggestUrl;
	}
}