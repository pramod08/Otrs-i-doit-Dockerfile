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
namespace idoit\Module\Search\Index\Protocol;

/**
 * i-doit
 *
 * Search document
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface Document
{

    /**
     * Documents data.
     *
     * Plain key => value array structure.
     *
     * e.g. ['cpu.1.frequency' => 2127, 'cpu.1.title' => 'Xeon E7430']
     *
     * @return array
     */
    public function getData();

    /**
     * Documents id. (Object ID for CMDB indexes)
     *
     * @return int
     */
    public function getId();

    /**
     * Document title. (Object title for CMDB indexes)
     *
     * @return string
     */
    public function getTitle();

    /**
     * The Module of this search document. E.g. cmdb, or viva.
     *
     * @return string
     */
    public function getType();

}