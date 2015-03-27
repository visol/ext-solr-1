<?php
namespace ApacheSolrForTypo3\Solr\ViewHelpers\Widget;

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
use ApacheSolrForTypo3\Solr\Widget\AbstractWidgetViewHelper;

/**
 * Class ResultPaginateViewHelper
 */
class ResultPaginateViewHelper extends AbstractWidgetViewHelper {

	/**
	 * @var \ApacheSolrForTypo3\Solr\ViewHelpers\Widget\Controller\ResultPaginateController
	 * @inject
	 */
	protected $controller;

	/**
	 * @param \Tx_Solr_Search $search
	 * @param string $as
	 * @param array $configuration
	 * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
	 * @throws \TYPO3\CMS\Fluid\Core\Widget\Exception\MissingControllerException
	 */
	public function render(\Tx_Solr_Search $search, $as = 'documents', array $configuration = array('insertAbove' => TRUE, 'insertBelow' => TRUE, 'maximumNumberOfLinks' => 10)) {
		return $this->initiateSubRequest();
	}

}