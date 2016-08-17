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
 * Class isys_report_view
 */
abstract class isys_report_view implements isys_report_view_interface
{
    /**
     *
     */
    const c_sql_view = 1;
    /**
     *
     */
    const c_php_view = 2;

    /**
     * @return mixed
     */
    abstract public function ajax_request();

    /**
     * @return mixed
     */
    abstract public function start();

    /**
     * Returns the report-view's template.
     *
     * @return  null
     */
    public function template()
    {
        return null;
    } // function

    /**
     * Determines, if a report view is brought in by an external source (module?).
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function external()
    {
        return false;
    } // function

    /**
     * Naked constructor.
     */
    public function __construct()
    {
        ;
    } // function
}

/**
 * i-doit Report Manager Views
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   Copyright 2012 - synetics GmbH
 */
interface isys_report_view_interface
{
    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public static function name();

    /**
     * @return mixed
     */
    public static function description();

    /**
     * @return mixed
     */
    public static function type();

    /**
     * @return mixed
     */
    public static function viewtype();
} // class