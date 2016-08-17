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
namespace idoit\Module\Search\Query\Protocol;

/**
 * i-doit
 *
 * Query result protocol
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface QueryResultItem
{
    /**
     * Get document Id
     *
     * @return int
     */
    public function getDocumentId();

    /**
     * Get key
     *
     * @return string
     */
    public function getKey();

    /**
     * Return URL to result item
     *
     * @return string
     */
    public function getLink();

    /**
     * Get matching score
     *
     * @return float
     */
    public function getScore();

    /**
     * Get type (module identifier)
     *
     * @return string
     */
    public function getType();

    /**
     * Get fulltext value
     *
     * @return string
     */
    public function getValue();

    /**
     * QueryResultItem constructor.
     *
     * @param int    $documentId
     * @param string $key
     * @param string $value
     * @param double $score
     */
    public function __construct($documentId, $key, $value, $score, array $conditions);

}