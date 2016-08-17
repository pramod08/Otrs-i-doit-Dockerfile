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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_list_ng
{
    private $m_checkbox = true;
    private $m_class = "mainTable";
    private $m_colgroups = [];
    private $m_data = [];
    private $m_fulldata = [];
    private $m_headers = [];
    private $m_id_element = null;
    private $m_js_check_all = "CheckAllBoxes(this);";
    private $m_row_link = null;
    private $m_row_method = "modify_row";
    private $m_row_modifier = null;
    private $m_template = "content/list/index.tpl";

    /**
     * Method for retrieving the template.
     *
     * @return  string
     */
    public function get_template()
    {
        return $this->m_template;
    } // function

    /**
     * Method for setting a template.
     *
     * @param   string $p_template
     *
     * @return  isys_component_list_ng
     */
    public function set_template($p_template)
    {
        global $g_comp_template;

        $this->m_template = $p_template;
        $g_comp_template->assign("list_template", $this->get_template());

        return $this;
    } // function

    /**
     * Method for retrieving the defined CSS class.
     *
     * @return  string
     */
    public function get_class()
    {
        return $this->m_class;
    } // function

    /**
     * Method for setting a CSS class.
     *
     * @param   string $p_class
     *
     * @return  isys_component_list_ng
     */
    public function set_class($p_class)
    {
        $this->m_class = $p_class;

        return $this;
    } // function

    /**
     * Retrieves the JS check function.
     *
     * @return  string
     */
    public function get_js_check_all()
    {
        return $this->m_js_check_all;
    } // function

    /**
     * Sets a JS function as check.
     *
     * @param   string $p_js_check_all
     *
     * @return  isys_component_list_ng
     */
    public function set_js_check_all($p_js_check_all)
    {
        $this->m_js_check_all = $p_js_check_all;

        return $this;
    } // function

    /**
     * Retrieves the data.
     *
     * @return  array
     */
    public function get_data()
    {
        return $this->m_data;
    } // function

    /**
     * Retrieves the full data by a given ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_fulldata($p_id = null)
    {
        if ($p_id === null)
        {
            return $this->m_fulldata;
        }
        else
        {
            return (isset($this->m_fulldata[$p_id])) ? $this->m_fulldata[$p_id] : [];
        } // if
    } // function

    /**
     * Retrieves all defined colgroups.
     *
     * @return  array
     */
    public function get_colgroups()
    {
        return $this->m_colgroups;
    } // function

    /**
     * Sets the colgroups for this list.
     *
     * @param   array $p_colgroups
     *
     * @return  isys_component_list_ng
     */
    public function set_colgroups($p_colgroups)
    {
        $this->m_colgroups = $p_colgroups;

        return $this;
    } // function

    /**
     * Retrieves the elements ID.
     *
     * @return  string
     */
    public function get_id_element()
    {
        return $this->m_id_element;
    } // function

    /**
     * Set the element ID.
     *
     * @param   string $p_id_element
     *
     * @return  isys_component_list_ng
     */
    public function set_id_element($p_id_element)
    {
        $this->m_id_element = $p_id_element;

        return $this;
    } // function

    /**
     * Returns a boolean value, if the checkboxes are enabled or not.
     *
     * @return  boolean
     */
    public function checkbox_enabled()
    {
        return $this->m_checkbox;
    } // function

    /**
     * Method for enabling the checkboxes.
     *
     * @return  isys_component_list_ng
     */
    public function enable_checkbox()
    {
        $this->m_checkbox = true;

        return $this;
    } // function

    /**
     * Method for disabling the checkboxes.
     *
     * @return  isys_component_list_ng
     */
    public function disable_checkbox()
    {
        $this->m_checkbox = false;

        return $this;
    } // function

    /**
     * Retrieves all defined headers.
     *
     * @return  array
     */
    public function get_headers()
    {
        return $this->m_headers;
    } // function

    /**
     * @param   array $p_array
     *
     * @return  isys_component_list_ng
     */
    public function set_headers($p_array)
    {
        $this->m_headers = $p_array;

        return $this;
    } // function

    /**
     * Retrieves the order-by string (ASC or DESC).
     *
     * @return  string
     */
    public function get_order()
    {
        return isys_glob_get_order();
    } // function

    /**
     * Sets a custom row modifier which is called when generating the list and has to have the method modify_row(inout $p_row) implemented.
     *
     * @param   mixed   $p_object
     * @param   boolean $p_method
     *
     * @return  isys_component_list_ng
     * @author  Dennis Stuecken
     */
    public function set_row_modifier($p_object, $p_method = false)
    {
        if (is_object($p_object))
        {
            $this->m_row_modifier = $p_object;
        } // if

        if ($p_method && method_exists($p_object, $p_method))
        {
            $this->m_row_method = $p_method;
        } // if

        return $this;
    } // function

    /**
     * Method for preparing the list.
     *
     * @param   mixed $p_data May be an array or isys_component_dao_result.
     *
     * @return  isys_component_list_ng
     */
    public function process_list($p_data)
    {
        global $g_comp_template;

        if (is_array($p_data))
        {
            $this->__prepare_list_array($p_data);
        }
        else if (is_object($p_data))
        {
            $this->__prepare_list_dao($p_data);
        } // if

        if ($this->m_id_element === null)
        {
            $this->disable_checkbox();
        } // if

        $g_comp_template->assign("list_template", $this->get_template())
            ->assign("list_display", true)
            ->assignByRef("list_object", $this);

        return $this;
    } // function

    /**
     * Retrieves the rendered template.
     *
     * @return  string
     * @throws  Exception
     */
    public function get_html_list()
    {
        if (is_array($this->m_data))
        {
            global $g_comp_template;

            return $g_comp_template->fetch($this->m_template);
        }
        else
        {
            throw new Exception("List has not been processed, yet.");
        } // if
    } // function

    /**
     * Method for replacing links inside the list.
     *
     * @param   string  $p_strString
     * @param   array   $p_arRow
     * @param   integer $p_max_count
     *
     * @return  mixed
     */
    protected function replace_link($p_strString, $p_arRow, $p_max_count = 10)
    {
        $i = 0;
        while (preg_match("/\[\{(.*?)\}\]/i", $p_strString, $l_reg))
        {
            if (isset($p_arRow[$l_reg[1]]))
            {
                $p_strString = str_replace("[{" . $l_reg[1] . "}]", $p_arRow[$l_reg[1]], $p_strString);
            } // if

            if (++$i == $p_max_count)
            {
                break;
            } // if
        } // while

        return $p_strString;
    } // function

    /**
     * @param   array $p_data
     *
     * @return  array
     */
    private function map_headers($p_data)
    {
        $l_return = [];

        if (is_array($this->m_headers))
        {
            foreach ($this->m_headers as $l_header_key => $l_header_value)
            {
                /**
                 * Custom row modifier.. (Not need to be a table_list..)
                 *
                 * @author Dennis Stuecken
                 */
                if (is_object($this->m_row_modifier))
                {
                    if (method_exists($this->m_row_modifier, $this->m_row_method))
                    {
                        $l_method = $this->m_row_method;
                        $this->m_row_modifier->$l_method($p_data);
                    } // if
                } // if

                $l_return[$l_header_key] = $p_data[$l_header_key];
            } // foreach
        } // if

        $l_return["__link"] = $this->replace_link($this->m_row_link, $p_data);

        return $l_return;
    } // function

    /**
     * Method for preparing the list by DAO data.
     *
     * @param   isys_component_dao_result $p_dao
     *
     * @return  isys_component_list_ng
     */
    private function __prepare_list_dao(isys_component_dao_result $p_dao)
    {
        isys_component_template_navbar::getInstance()
            ->set_page_results($p_dao->count());

        while ($l_row = $p_dao->get_row())
        {
            $this->m_fulldata[] = $l_row;
            $this->m_data[]     = $this->map_headers($l_row);
        } // while

        return $this;
    } // function

    /**
     * Method for preparing the list by array data.
     *
     * @param   array $p_array
     *
     * @return  isys_component_list_ng
     */
    private function __prepare_list_array(array $p_array = [])
    {
        isys_component_template_navbar::getInstance()
            ->set_page_results(count($p_array));

        $this->m_fulldata = $p_array;

        foreach ($p_array as $l_row)
        {
            $this->m_data[] = $this->map_headers($l_row);
        } // foreach

        return $this;
    } // function

    /**
     * @param  array  $p_headers
     * @param  mixed  $p_data May be an array or isys_component_dao_result.
     * @param  null   $p_checkbox_id_element
     * @param  string $p_row_link
     * @param  string $p_template
     */
    public function __construct($p_headers = null, $p_data = null, $p_checkbox_id_element = null, $p_row_link = null, $p_template = null)
    {
        $this->m_id_element = $p_checkbox_id_element;
        $this->m_row_link   = $p_row_link;

        if ($p_template !== null)
        {
            $this->set_template($p_template);
        } // if

        if ($p_headers !== null)
        {
            $this->set_headers($p_headers);
        } // if

        if ($p_data !== null)
        {
            $this->process_list($p_data);
        } // if
    } // function
} // class