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
namespace idoit\Module\Search\Index\Traits;

/**
 * i-doit
 *
 * Search index counter trait
 *
 * Provides index counter member variables
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
trait IndexCounterTrait
{
    /**
     * Amount of indexed documents
     *
     * @var int
     */
    private $indexedDocuments = 0;

    /**
     * Amount of indexed property values
     *
     * @var int
     */
    private $indexedItems = 0;

    /**
     * @return int
     */
    public function getIndexedItems()
    {
        return $this->indexedItems;
    }

    /**
     * @return int
     */
    public function getIndexedDocuments()
    {
        return $this->indexedDocuments;
    }
}