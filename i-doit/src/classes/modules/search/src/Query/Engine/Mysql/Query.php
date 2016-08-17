<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.i-doit.com/license for a full copyright and license information.
 *
 * @version     1.7.3
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.i-doit.com/license
 */
namespace idoit\Module\Search\Query\Engine\Mysql;

use idoit\Model\Dao\Base;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Engine\AbstractQuery;
use idoit\Module\Search\Query\Protocol\Query as QueryProtocol;
use idoit\Module\Search\Query\QueryResult;
use isys_component_database as Database;
use isys_tenantsettings as TenantSettings;

/**
 * i-doit
 *
 * MySQL Search Query
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Query extends AbstractQuery implements QueryProtocol
{

    /**
     * @var Base
     */
    private $dao;

    /**
     * Boolean Syntax
     * Result of "show global variables like 'ft_boolean_syntax';"
     *
     * @var string
     */
    private $ftsTerm = '+ -><()~*:""&|';

    /**
     * SQL Statement
     *
     * @var string
     */
    private $statement = '';

    /**
     * Sanitize Keyword: Prevent SQL Errors, strip boolean syntax from keywords.
     *
     * @param string $keyword
     *
     * @return string
     */
    private function sanitizeForBoolMatching($keyword = '')
    {
        if ($keyword && preg_match('/[' . $this->ftsTerm . ']/', $keyword))
        {
            // Fixing error "syntax error, unexpected $end, expecting FTS_TERM or FTS_NUMB or '*'" with str_replace and rtrim
            $keyword = trim(
                ltrim(
                    rtrim(
                        str_replace(
                            [
                                '-*',
                                '**'
                            ],
                            '*',
                            $keyword
                        ),
                        $this->ftsTerm
                    ),
                    $this->ftsTerm
                )
            );

            // Replace all condition keywords by a space
            // Currently untested and not suggested
            //$conditionKeyword = str_replace(str_split($ftsTermSanitize), ' ', $conditionKeyword);
        }

        return $keyword;
    }

    /**
     * Prepare like condition
     *
     * @param      $keyword
     * @param bool $negation
     *
     * @return string
     */
    private function like($keyword, $negation = false)
    {
        return sprintf(
            '(isys_search_idx__value %s \'%s\')',
            $negation ? 'NOT LIKE' : 'LIKE',
            '%' . $keyword . '%'
        );
    }

    /**
     * Query Database and search for given conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions)
    {
        $matchers  = $matchers2 = [];
        $result    = new QueryResult($conditions);
        $likeMatch = [];

        foreach ($conditions as $condition)
        {
            $conditionKeyword = trim($condition->getKeyword());

            // Sanitize and split Keywords by space into independent strings
            $keywordSplit = explode(' ', $this->sanitizeForBoolMatching($conditionKeyword));

            foreach ($keywordSplit as $keyword)
            {
                if (trim($keyword) === '')
                {
                    continue;
                }

                if ($condition->getMode() === Condition::MODE_DEEP)
                {
                    // Prepare like condition for each keyword if search is operated in deep search mode
                    $likeMatch[] = $this->like($keyword, $condition->isNegation());
                }
                else
                {
                    // Add keyword rule to the matchers array
                    if ($condition->isNegation() || $keyword[0] === '-')
                    {
                        // Negate by prepending a minus
                        $matchers[] = '-' . $keyword . '*';
                    }
                    else
                    {
                        $matchers[] = $keyword . '*';
                    }

                    // Add another > matching rule, as suggested by Percona.
                    $matchers2[] = ' >"' . $keyword . '"';
                }
            }
        }

        // Prepare boolean fulltext matching
        $scoreMatching = $matching = count($matchers) ? '(MATCH(isys_search_idx__value) AGAINST (' . $this->dao->convert_sql_text(
                implode(' ', $matchers) . implode(' ', $matchers2)
            ) . ' IN BOOLEAN MODE))' : '';

        if (!$scoreMatching) $scoreMatching = '66';
        if ($matching) $matching = 'WHERE ' . $matching;

        /** Additionally matching over the search result via HAVING LIKE, since there are problems with ftsSearchTerm separated keywords.. */
        /** @see https://i-doit.atlassian.net/wiki/pages/viewpage.action?pageId=30441489 */
        $likeMatchCondition = count($likeMatch) ? 'WHERE ' . implode(' AND ', $likeMatch) : '';

        $daoResult = $this->dao->retrieve(
            sprintf($this->statement, $scoreMatching, $matching, $likeMatchCondition, TenantSettings::get('search.limit', '2500'))
        );

        while ($row = $daoResult->get_row())
        {
            foreach ($result->getConditions() as $condition)
            {
                if ($condition->getMode() !== Condition::MODE_FUZZY && !stristr($row['searchValue'], $condition->getKeyword()))
                {
                    continue 2;
                }
            }

            $result->addItem(
                $this->getQueryItemInstance(
                    $row['type'],
                    $row['searchReference'],
                    $row['searchKey'],
                    $row['searchValue'],
                    $row['searchScore'] * 1.5,
                    $conditions
                )
            );
        }

        return $result;
    }

    /**
     * Mysql constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->dao       = new Base($database);
        $this->statement = 'SELECT
isys_search_idx__type AS type, isys_search_idx__key AS searchKey,
(CASE isys_obj__title WHEN isys_search_idx__value THEN isys_search_idx__value ELSE CONCAT(isys_obj__title, ": ", isys_search_idx__value) END)
AS searchValue, isys_search_idx__reference AS searchReference, %s AS searchScore
FROM isys_search_idx LEFT JOIN isys_obj ON isys_obj__id = isys_search_idx__reference %s
%s ORDER BY searchScore DESC LIMIT %s;';
    }

}