<?php
namespace ApacheSolrForTypo3\Solr\Widget;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 01-04-2015 10:08
 * All code (c) Beech Applications B.V. all rights reserved
 */

/**
 * Class AbstractWidgetController
 */
class AbstractWidgetController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $supportedRequestTypes = array('ApacheSolrForTypo3\\Solr\\Widget\\WidgetRequest');
}