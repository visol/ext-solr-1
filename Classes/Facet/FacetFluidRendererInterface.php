<?php
namespace ApacheSolrForTypo3\Solr\Facet;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 30-03-2015 15:51
 * All code (c) Beech Applications B.V. all rights reserved
 */

/**
 * Class FacetFluidRendererInterface
 */
interface FacetFluidRendererInterface {

	/**
	 * Renders the complete facet.
	 *
	 * @return string The rendered facet
	 */
	public function renderFacet();

	/**
	 * Provides the internal type of facets the renderer handles.
	 * The type is one of field, range, or query.
	 *
	 * @return string Facet internal type
	 */
	public static function getFacetInternalType();

	/**
	 * Gets the facet object markers for use in templates.
	 *
	 * @return array An array with facet object markers.
	 */
	public function getFacetProperties();

	/**
	 * Gets the facet's options
	 *
	 * @return array An array with facet options.
	 */
	public function getFacetOptions();

	/**
	 * Gets the number of options for a facet.
	 *
	 * @return integer Number of facet options for the current facet.
	 */
	public function getFacetOptionsCount();

}