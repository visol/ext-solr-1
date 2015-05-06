<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Debug;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 02-04-2015 10:08
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class DocumentScoreAnalyzerViewHelper
 */
class DocumentScoreAnalyzerViewHelper extends AbstractViewHelper {

	/**
	 * @var \Tx_Solr_Search
	 */
	protected $search;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// todo: fetch from ControllerContext
		$this->configuration = \Tx_Solr_Util::getSolrConfiguration();
		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search');
	}

	/**
	 * Get document relevance percentage
	 *
	 * @param \Apache_Solr_Document $document
	 * @return string
	 */
	public function render(\Apache_Solr_Document $document) {
		$content = '';

		// only check whether a BE user is logged in, don't need to check
		// for enabled score analysis as we wouldn't be here if it was disabled
		if (!empty($GLOBALS['TSFE']->beUserLogin)) {
			$debugData  = $this->search->getDebugResponse()->explain->{$document->getId()};
			$highScores = $this->getHighScores($debugData);
			$score = $document->getField('score');
			$content = $this->renderScoreAnalysis($highScores, (float)($score ? $score['value'] : 0), $debugData);
		}

		return $content;
	}

	/**
	 * Get high scores
	 *
	 * @param string $debugData
	 * @return array
	 */
	protected function getHighScores($debugData) {
		$highScores = array();

		/* TODO Provide better parsing
		 *
		 * parsing could be done line by line,
		 * 		* recording indentation level
		 * 		* replacing abbreviations
		 * 		* replacing phrases like "product of" by mathematical symbols (* or x)
		 * 		* ...
		 */

		// matches search term weights, ex: 0.42218783 = (MATCH) weight(content:iPod^40.0 in 43), product of:
		$pattern = '/(.*) = \(MATCH\) weight\((.*)\^/';
		$matches = array();
		preg_match_all($pattern, $debugData, $matches);

		foreach($matches[0] as $key => $value) {
			// split field from search term
			list($field, $searchTerm) = explode(':', $matches[2][$key]);

			// keep track of highest score per search term
			if (!isset($highScores[$field])
				|| $highScores[$field]['score'] < $matches[1][$key]) {
				$highScores[$field] = array(
					'score'      => $matches[1][$key],
					'field'      => $field,
					'searchTerm' => $searchTerm
				);
			}
		}

		// Function query
		$pattern = '/(.*) = \(MATCH\) FunctionQuery\((.*)\),/';
		preg_match($pattern, $debugData, $match);
		if (!empty($match[1])) {
			$field = 'FunctionQuery (misses boost value)';
			$highScores[$field] = array(
				'score'      => $match[1],
				'field'      => $field,
				'searchTerm' => $match[2]
			);
		}

		return $highScores;
	}

	/**
	 * Renders an overview of how the score for a certain document has been
	 * calculated.
	 *
	 * @param array $highScores The result document which to analyse
	 * @param float $realScore
	 * @param string $debugData
	 * @return string The HTML showing the score analysis
	 */
	protected function renderScoreAnalysis(array $highScores, $realScore, $debugData) {
		$scores = array();
		$totalScore = 0;

		foreach ($highScores as $field => $highScore) {
			$pattern = '/' . $highScore['field'] . '\^([\d.]*)/';
			$matches = array();
			preg_match_all($pattern, $this->configuration['search.']['query.']['queryFields'], $matches);

			$scores[] = '
				<td>+ ' . $highScore['score'] . '</td>
				<td>' . $highScore['field'] . '</td>
				<td>' . $matches[1][0] . '</td>';

			$totalScore += $highScore['score'];
		}

		$content = '<table class="document-score-analysis" style="width: 100%; border: 1px solid #aaa; font-size: 11px; background-color: #eee;">
			<tr style="border-bottom: 2px solid #aaa; font-weight: bold;"><td>Score</td><td>Field</td><td>Boost</td></tr><tr>'
			. implode('</tr><tr>', $scores)
			. '</tr>
			<tr><td colspan="3"><hr style="border-top: 1px solid #aaa; height: 0px; padding: 0px; margin: 0px;" /></td></tr>
			<tr><td colspan="3">= ' . $totalScore . ' (Inaccurate analysis! Not all parts of the score have been taken into account.)</td></tr>
			<tr><td colspan="3">= ' . $realScore . ' (real score)</td></tr>
			<tr class="raw" style="display:none"><td colspan="3"><pre>' . htmlspecialchars($debugData) . '</pre></td></tr>
			</table>';

		return $content;
	}
}