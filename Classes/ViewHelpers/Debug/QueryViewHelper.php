<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Debug;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class QueryViewHelper
 */
class QueryViewHelper extends AbstractViewHelper {

	/**
	 * @var \Tx_Solr_Search
	 */
	protected $search;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search');
	}

	/**
	 * Get parsed query debug output
	 * only visible for logged in BE users
	 *
	 * @return string
	 */
	public function render() {
		$content = '';
		if (!empty($GLOBALS['TSFE']->beUserLogin)) {
			$content = '<br><strong>Parsed Query:</strong><br>' .
				$this->search->getDebugResponse()->parsedquery;
		}
		return $content;
	}
}