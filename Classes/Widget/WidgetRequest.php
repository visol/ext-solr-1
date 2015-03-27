<?php
namespace ApacheSolrForTypo3\Solr\Widget;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 01-04-2015 10:05
 * All code (c) Beech Applications B.V. all rights reserved
 */

/**
 * Class WidgetRequest
 */
class WidgetRequest extends \TYPO3\CMS\Fluid\Core\Widget\WidgetRequest {

	/**
	 * Returns the unique URI namespace for this widget in the format pluginNamespace[widgetIdentifier]
	 *
	 * @return string
	 */
	public function getArgumentPrefix() {
		// we skip the [@widget] part
		return $this->widgetContext->getParentPluginNamespace();
	}
}