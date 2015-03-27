<?php
namespace ApacheSolrForTypo3\Solr\Controller;

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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class BaseController
 */
class BaseController extends ActionController {

	/**
	 * @var string
	 */
	protected $pluginName = 'pi';

	/**
	 * @var ContentObjectRenderer
	 */
	protected $contentObjectRenderer;

	/**
	 * @var TypoScriptFrontendController
	 */
	protected $typoScriptFrontendController;

	/**
	 * An instance of Tx_Solr_Search
	 *
	 * @var \Tx_Solr_Search
	 */
	protected $search;

	/**
	 * The plugin's query
	 *
	 * @var \Tx_Solr_Query
	 */
	protected $query = NULL;

	/**
	 * Full typoscript configuration
	 *
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Determines whether the solr server is available or not.
	 */
	protected $solrAvailable;

	/**
	 * The user's raw query
	 *
	 * @var string
	 */
	protected $rawUserQuery;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

		$fullTypoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$this->configuration = ArrayUtility::arrayMergeRecursiveOverrule(
			$fullTypoScript['plugin.']['tx_solr.'],
			(array)$fullTypoScript['plugin.']['tx_solr_pi_results.'] // todo: check what to do with this. Do we need fallback to old typoscript config? define used name by called action?
		);

		// todo: add flexform overrides
		//		$this->overrideTyposcriptWithFlexformSettings();

		$this->contentObjectRenderer = $this->configurationManager->getContentObject();
	}

	/**
	 * Initialize action
	 */
	protected function initializeAction() {
		parent::initializeAction();
		$this->typoScriptFrontendController = $GLOBALS['TSFE'];
		$this->initializeQuery();
		$this->initializeSearch();
	}

	/**
	 * Initializes the query from the GET query parameter.
	 *
	 */
	protected function initializeQuery() {
		$this->rawUserQuery = GeneralUtility::_GET('q');
	}

	/**
	 * Initialize the Solr connection and
	 * test the connection through a ping
	 */
	protected function initializeSearch() {
		/** @var \Tx_Solr_ConnectionManager $solrConnection */
		$solrConnection = GeneralUtility::makeInstance('Tx_Solr_ConnectionManager')->getConnectionByPageId(
			$this->typoScriptFrontendController->id,
			$this->typoScriptFrontendController->sys_language_uid,
			$this->typoScriptFrontendController->MP
		);

		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search', $solrConnection);
		$this->solrAvailable = $this->search->ping();
	}

	/**
	 * Get rawUserQuery
	 *
	 * @return string
	 */
	public function getRawUserQuery() {
		return $this->rawUserQuery;
	}
}