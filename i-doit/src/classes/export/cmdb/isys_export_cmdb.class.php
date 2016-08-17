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

/**
 * @package     i-doit
 * @subpackage  Export CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_export_cmdb extends isys_export
{
    /**
     * @var  mixed
     */
    protected $m_export;

    /**
     * Export method.
     *
     * @abstract
     *
     * @param  array $p_object_ids
     */
    abstract public function export($p_object_ids); // function

    public function get_export()
    {
        return $this->m_export;
    } // function

    public function set_export($p_export)
    {
        $this->m_export = $p_export;
    }

    public function __construct(&$p_export_type, isys_component_database &$p_database = null)
    {
        parent::__construct($p_export_type, $p_database);
    } // function
} // class
?>