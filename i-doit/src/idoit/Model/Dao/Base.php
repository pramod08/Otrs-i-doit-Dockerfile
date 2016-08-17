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
namespace idoit\Model\Dao;

/**
 * i-doit Model.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Base extends \isys_component_dao
{

    /**
     * Implode array of selectable columns.
     *
     * @param   array $mapping
     *
     * @return  string
     */
    public function selectImplode(array $mapping)
    {
        if (count($mapping) > 0)
        {
            array_walk(
                $mapping,
                function (&$item, $key)
                {
                    $item = $key . ' AS ' . $item;
                }
            );

            return implode(', ', $mapping);
        } // if

        return '*';
    } // function
} // class