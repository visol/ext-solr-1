<?php
namespace ApacheSolrForTypo3\Solr\Facet;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 30-03-2015 15:55
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ApacheSolrForTypo3\Solr\View\StandaloneView;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Class AbstractFacetFluidRenderer
 */
abstract class AbstractFacetFluidRenderer extends \Tx_Solr_Facet_AbstractFacetRenderer implements FacetFluidRendererInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 */
	protected $typoScriptService;

	/**
	 * @var array TypoScript settings
	 */
	protected $settings = array();

	/**
	 * @var StandaloneView
	 */
	protected $view;

	/**
	 * Constructor
	 *
	 * @param \Tx_Solr_Facet_Facet $facet The facet to render.
	 */
	public function __construct(\Tx_Solr_Facet_Facet $facet) {
		$this->search = GeneralUtility::makeInstance('Tx_Solr_Search');

		$this->facet              = $facet;
		$this->facetName          = $facet->getName();

		$this->solrConfiguration  = \Tx_Solr_Util::getSolrConfiguration();
		$this->facetConfiguration = $this->solrConfiguration['search.']['faceting.']['facets.'][$this->facetName . '.'];

		$this->typoScriptService = GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Extbase\\Service\\TypoScriptService'
		);
		$this->settings = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
			\Tx_Solr_Util::getSolrConfiguration()
		);
		$this->initView();
	}

	/**
	 * Init view
	 */
	protected function initView() {
		/** @var StandaloneView view */
		$this->view = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\View\\StandaloneView');
		$paths = $this->settings['view']['layoutRootPaths'];
		$this->view->setLayoutRootPaths($this->fixPaths($paths ?: array('EXT:solr/Resources/Private/Layouts')));
		$paths = $this->settings['view']['partialRootPaths'];
		$this->view->setPartialRootPaths($this->fixPaths($paths ?: array('EXT:solr/Resources/Private/Partials')));
		$paths = $this->settings['view']['templateRootPaths'];
		$this->view->setTemplateRootPaths($this->fixPaths($paths ?: array('EXT:solr/Resources/Private/templates')));
	}

	/**
	 * Get abs paths
	 *
	 * @param array $paths
	 * @return array
	 */
	protected function fixPaths($paths) {
		foreach ($paths as $key => $path) {
			$paths[$key] = GeneralUtility::getFileAbsFileName($path);
		}
		return $paths;
	}

	/**
	 * Renders the complete facet.
	 *
	 * @return	string	Facet markup.
	 */
	public function renderFacet() {

		$facetContent = '';

		$showEmptyFacets = FALSE;
		if (!empty($this->solrConfiguration['search.']['faceting.']['showEmptyFacets'])) {
			$showEmptyFacets = TRUE;
		}

		$showEvenWhenEmpty = FALSE;
		if (!empty($this->solrConfiguration['search.']['faceting.']['facets.'][$this->facetName . '.']['showEvenWhenEmpty'])) {
			$showEvenWhenEmpty = TRUE;
		}

		// if the facet doesn't provide any options, don't render it unless
		// it is configured to be rendered nevertheless
		if (!$this->facet->isEmpty() || $showEmptyFacets || $showEvenWhenEmpty) {

			try {
				$this->view->setTemplateName('Facets/' . ($this->facetConfiguration['fluid.']['template'] ?: 'Default'));
			} catch(InvalidTemplateResourceException $e) {
				return $e->getMessage();
			}
			/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject */
			$contentObject = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			$label = $contentObject->stdWrap(
				$this->facetConfiguration['label'],
				$this->facetConfiguration['label.']
			);
			$this->view->assign('label', $label);
			$this->view->assign('facet', $this->facet);
			$this->view->assign('settings', $this->settings);
			$this->renderFacetOptions();
			$facetContent = $this->view->render();
		}

		return $facetContent;
	}
}