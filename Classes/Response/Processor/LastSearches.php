<?php
namespace ApacheSolrForTypo3\Solr\Response\Processor;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2015 Ingo Renner <ingo@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use ApacheSolrForTypo3\Solr\Query;
use ApacheSolrForTypo3\Solr\Util;

/**
 * Logs the keywords from the query into the user's session or the database -
 * depending on configuration.
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @package TYPO3
 * @subpackage solr
 */
class LastSearches implements ResponseProcessor
{

    /**
     * @var string
     */
    protected $prefix = 'tx_solr';

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $feUser;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * Constructor
     */
    public function __construct()
    {
        // todo: fetch from ControllerContext
        $this->configuration = Util::getSolrConfiguration();
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->typoScriptFrontendController = $GLOBALS['TSFE'];
        $this->feUser = $this->typoScriptFrontendController->fe_user;
    }

    /**
     * Does not actually modify the result set, but tracks the search keywords.
     *
     * @param Query $query
     * @param \Apache_Solr_Response $response
     * @throws \UnexpectedValueException
     */
    public function processResponse(
        Query $query,
        \Apache_Solr_Response $response
    ) {
        $keywords = $query->getKeywordsCleaned();

        $keywords = trim($keywords);
        if (empty($keywords)) {
            return;
        }

        switch ($this->configuration['search.']['lastSearches.']['mode']) {
            case 'user':
                $this->storeKeywordsToSession($keywords);
                break;
            case 'global':
                $this->storeKeywordsToDatabase($keywords);
                break;
            default:
                throw new \UnexpectedValueException(
                    'Unknown mode for plugin.tx_solr.search.lastSearches.mode, valid modes are "user" or "global".',
                    1342456570
                );
        }
    }

    /**
     * Stores the keywords from the current query to the user's session.
     *
     * @param string $keywords The current query's keywords
     * @return void
     */
    protected function storeKeywordsToSession($keywords)
    {
        $currentLastSearches = $this->feUser->getKey(
            'ses',
            $this->prefix . '_lastSearches'
        );

        if (!is_array($currentLastSearches)) {
            $currentLastSearches = array();
        }

        $lastSearches = $currentLastSearches;
        $newLastSearchesCount = array_push($lastSearches, $keywords);

        while ($newLastSearchesCount > $this->configuration['search.']['lastSearches.']['limit']) {
            array_shift($lastSearches);
            $newLastSearchesCount = count($lastSearches);
        }

        $this->feUser->setKey(
            'ses',
            $this->prefix . '_lastSearches',
            $lastSearches
        );
    }

    /**
     * Stores the keywords from the current query to the database.
     *
     * @param string $keywords The current query's keywords
     * @return void
     */
    protected function storeKeywordsToDatabase($keywords)
    {
        $nextSequenceId = $this->getNextSequenceId();

        // TODO try to add a execREPLACEquery to t3lib_db
        $this->databaseConnection->sql_query(
            'INSERT INTO tx_solr_last_searches (sequence_id, tstamp, keywords)
            VALUES ('
            . $nextSequenceId . ', '
            . time() . ', '
            . $this->databaseConnection->fullQuoteStr($keywords,
                'tx_solr_last_searches')
            . ')
            ON DUPLICATE KEY UPDATE tstamp = ' . time() . ', keywords = ' . $this->databaseConnection->fullQuoteStr($keywords,
                'tx_solr_last_searches')
        );
    }

    /**
     * Gets the sequence id for the next search entry.
     *
     * @return integer The id to be used as the next sequence id for storing the last search keywords.
     */
    protected function getNextSequenceId()
    {
        $nextSequenceId = 0;
        $numberOfLastSearchesToLog = $this->configuration['search.']['lastSearches.']['limit'];

        $row = $this->databaseConnection->exec_SELECTgetRows(
            '(sequence_id + 1) % ' . $numberOfLastSearchesToLog . ' as next_sequence_id',
            'tx_solr_last_searches',
            '',
            '',
            'tstamp DESC',
            1
        );

        if (!empty($row)) {
            $nextSequenceId = $row[0]['next_sequence_id'];
        }

        return $nextSequenceId;
    }
}

