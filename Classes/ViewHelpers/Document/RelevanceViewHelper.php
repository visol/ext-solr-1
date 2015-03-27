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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class RelevanceViewHelper
 */
class RelevanceViewHelper extends AbstractViewHelper {

	/**
	 * Get document relevance percentage
	 *
	 * @param \Tx_Solr_Search $search
	 * @param \Apache_Solr_Document $document
	 * @return int
	 */
	public function render(\Tx_Solr_Search $search, \Apache_Solr_Document $document) {

		$maximumScore = $document->__solr_grouping_groupMaximumScore ?: $search->getMaximumResultScore();
		$documentScore = $document->getScore();
		$content = 0;
		if ($maximumScore > 0) {
			$score = floatval($documentScore);
			$scorePercentage = round($score * 100 / $maximumScore);
			$content = $scorePercentage;
		}
		return $content;
	}

}