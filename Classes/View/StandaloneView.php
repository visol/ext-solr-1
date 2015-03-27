<?php
namespace ApacheSolrForTypo3\Solr\View;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 30-03-2015 14:09
 * All code (c) Beech Applications B.V. all rights reserved
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Class StandaloneView
 *
 * Standalone viewHelper from core extended with templatePath resolving
 */
class StandaloneView extends \TYPO3\CMS\Fluid\View\StandaloneView {

	/**
	 * Path(s) to the template root
	 *
	 * @var array
	 */
	protected $templateRootPaths = NULL;

	/**
	 * @var array
	 */
	protected $templatePathCache = array();

	/**
	 * Set templateRootPaths
	 *
	 * @param array $templateRootPaths
	 */
	public function setTemplateRootPaths(array $templateRootPaths) {
		$this->templateRootPaths = $templateRootPaths;
	}

	/**
	 * @param $templateName
	 * @param bool $throwException
	 * @return null
	 * @throws InvalidTemplateResourceException
	 */
	public function setTemplateName($templateName, $throwException = TRUE) {
		$templateName = ucfirst($templateName);
		$cacheKey = 't_' . $templateName;

		if (!isset($this->templatePathCache[$cacheKey])) {

			$paths = ArrayUtility::sortArrayWithIntegerKeys($this->templateRootPaths);
			$paths = array_reverse($paths, TRUE);
			$possibleTemplatePaths = array();
			foreach ($paths as $templateRootPath) {
				$possibleTemplatePaths[] = GeneralUtility::fixWindowsFilePath($templateRootPath . '/' . $templateName . '.html');
				$possibleTemplatePaths[] = GeneralUtility::fixWindowsFilePath($templateRootPath . '/' . $templateName);
			}
			foreach ($possibleTemplatePaths as $templatePathAndFilename) {
				if ($this->testFileExistence($templatePathAndFilename)) {
					$this->templatePathCache[$cacheKey] = $templatePathAndFilename;
					break;
				}
			}
		}

		if (isset($this->templatePathCache[$cacheKey])) {
			return $this->setTemplatePathAndFilename($this->templatePathCache[$cacheKey]);
		} elseif ($throwException) {
			throw new InvalidTemplateResourceException('Could not load template file. Tried following paths: "' . implode('", "', $possibleTemplatePaths) . '".', 1413190242);
		} else {
			return NULL;
		}
	}
}