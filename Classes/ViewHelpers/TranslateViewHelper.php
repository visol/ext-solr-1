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

/**
 * Class TranslateViewHelper
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper {

	/**
	 * Wrapper function to support "old solr way" of doing translations
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function render() {
		if ($this->hasArgument('arguments')) {
			foreach ($this->arguments['arguments'] as $key => $value) {
				if (substr($key, 0, 1) === '@') {
					return $this->renderSolrTranslation(
						$this->hasArgument('id') ? $this->arguments['id'] : $this->arguments['key']
					);
				}
			}
		}
		return parent::render();
	}

	/**
	 * Translate a given key or use the tag body as default.
	 * Use strtr instead of vsprintf to replace the arguments
	 *
	 * @param string $id The locallang id
	 * @return string The translated key or tag body if key doesn't exist
	 */
	protected function renderSolrTranslation($id) {
		$request = $this->controllerContext->getRequest();
		$extensionName = $this->arguments['extensionName'] === NULL ? $request->getControllerExtensionName() : $this->arguments['extensionName'];
		$value = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, $extensionName, $this->arguments['arguments']);
		if ($value === NULL) {
			$value = $this->arguments['default'] !== NULL ? $this->arguments['default'] : $this->renderChildren();
			if (is_array($this->arguments['arguments'])) {
				$value = strtr($value, $this->arguments['arguments']);
			}
		} elseif ($this->arguments['htmlEscape']) {
			$value = htmlspecialchars($value);
		}
		return $value;
	}
}