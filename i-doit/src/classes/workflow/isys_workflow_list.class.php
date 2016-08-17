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
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_list extends isys_workflow
{

    /**
     * @desc singleton instance
     */
    private static $m_instance;
    /**
     * @var array
     */
    private $m_columns;

    /**
     * @var array
     */
    private $m_headers = [];

    /**
     * @var array
     */
    private $m_links;
    /**
     * @var array
     */
    private $m_tables;

    /**
     * Singleton instance.
     *
     * @return  isys_workflow_list
     */
    public static function get()
    {
        if (!isset(self::$m_instance))
        {
            $l_class          = __CLASS__;
            self::$m_instance = new $l_class;
            self::$m_instance->init();
        } // if

        return self::$m_instance;
    }

    public function add_column($p_index, $p_key, $p_title, $p_value)
    {
        if (!array_key_exists($p_key, $this->m_columns))
        {
            /*first, add the new header.. if its really new and not known already*/
            $this->add_header($p_title, $p_key);
            /*then, add the column with its content*/
            $this->m_columns[$p_index][$p_key] = $p_value;

            /*finally, return the index*/

            return $p_index;
        }
    }

    public function add_header($p_title, $p_key)
    {
        if (is_array($this->m_headers))
        {
            if (!array_key_exists($p_key, $this->m_headers))
            {
                $this->m_headers[$p_key] = [
                    "title"  => $p_title,
                    "active" => true
                ];
            }
        }
    }

    public function remove_header($p_key)
    {
        if (array_key_exists($p_key, $this->m_headers))
        {
            unset($this->m_headers[$p_key]);
        }
    }

    public function remove_column($p_index)
    {
        unset($this->m_columns[$p_index]);
    }

    public function get_tables()
    {
        return $this->m_tables;
    }

    public function get_columns()
    {
        return $this->m_columns;
    }

    public function get_headers()
    {
        return $this->m_headers;
    }

    public function get_links()
    {
        return $this->m_links;
    }

    public function set_link($p_col_index, $p_link)
    {
        $this->m_links[$p_col_index] = $p_link;
    }

    public function sort()
    {

        return true;
    }

    /**
     * @desc filter the workflow list
     *
     * @param        string /const/int $p_filter_type
     * @param string $p_filter
     *
     * @return boolean
     */
    public function filter($p_filter_type, $p_filter)
    {

        switch ($p_filter_type)
        {
            case "today":

                $l_columns = $this->get_columns();
                foreach ($l_columns as $l_key => $l_value)
                {

                    if ($l_value["isys_workflow_action_parameter__datetime"] != $p_filter)
                    {

                        $this->remove_column($l_key);

                    }

                }

                return true;

                break;
        }

        return false;
    }

    /**
     * @desc assign $this to smarty
     * @return boolean
     */
    public function assign()
    {
        global $g_comp_template;

        $g_comp_template->assign("g_post", $_POST)
            ->assign("order_dir", (empty($_POST['order_dir']) ? 'DESC' : ($_POST['order_dir'] == 'DESC') ? 'ASC' : 'DESC'))
            ->assign("order_field", (!empty($_POST['order_field']) ? $_POST['order_field'] : ''))
            ->assign("g_num_rows", count($this->m_columns));

        return $g_comp_template->registerObject("isys_workflow_list", $this);
    } // function

    /**
     *
     */
    public function init()
    {
        $this->m_columns = [];
        $this->m_tables  = [];
    } // function
} // class