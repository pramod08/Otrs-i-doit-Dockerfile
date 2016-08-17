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
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_idoit extends isys_component_dao
{
    /**
     * This array will be filled with the "isys_db_init" data.
     *
     * @var  null
     */
    private $m_isys_info = null;

    /**
     * Method for loading the data from "isys_db_init".
     *
     * @return  array
     */
    public function get_info()
    {
        if ($this->m_isys_info === null)
        {
            $l_title = $l_version = $l_revision = '';

            $l_res = $this->retrieve('SELECT * FROM isys_db_init;');

            while ($l_row = $l_res->get_row())
            {
                if ($l_row['isys_db_init__key'] == 'version')
                {
                    $l_version = $l_row['isys_db_init__value'];
                } // if

                if ($l_row['isys_db_init__key'] == 'revision')
                {
                    $l_revision = $l_row['isys_db_init__value'];
                } // if

                if ($l_row['isys_db_init__key'] == 'title')
                {
                    $l_title = $l_row['isys_db_init__value'];
                } // if
            } // while

            $this->m_isys_info = [
                'name'     => $l_title,
                'version'  => $l_version,
                'revision' => $l_revision
            ];
        } // if

        return $this->m_isys_info;
    } // function

    /**
     * Method for retrieving the revision number.
     *
     * @return  string
     */
    public function get_revision()
    {
        $l_data = $this->get_info();

        return $l_data["revision"];
    } // function

    /**
     * Method for retrieving the version name.
     *
     * @return  string
     */
    public function get_version_name()
    {
        $l_data = $this->get_info();

        return $l_data["name"];
    } // function

    /**
     * Method for retrieving the version number.
     *
     * @return  string
     */
    public function get_version()
    {
        $l_data = $this->get_info();

        return $l_data["version"];
    } // function
} // class
?>