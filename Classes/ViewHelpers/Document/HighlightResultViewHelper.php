<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Document;

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
 * Class HighlightResultViewHelper
 */
class HighlightResultViewHelper extends AbstractViewHelper {

	/**
	 * Get high lighted field
	 *
	 * @param \Apache_Solr_Document $document
	 * @param string $field
	 * @return string
	 */
	public function render(\Apache_Solr_Document $document, $field) {
		/** @var \Tx_Solr_Search $search */
		$search = GeneralUtility::makeInstance('Tx_Solr_Search');
		$configuration = \Tx_Solr_Util::getSolrConfiguration();
		$content = call_user_func(array($document, 'get' . $field));

		$highlightedContent = $search->getHighlightedContent();
		if (!empty($highlightedContent->{$document->getId()}->{$field}[0])) {
			$content = implode(
				' ' . $configuration['search.']['results.']['resultsHighlighting.']['fragmentSeparator'] . ' ',
				$highlightedContent->{$document->getId()}->{$field}
			);
		}
		return $content;
	}
}