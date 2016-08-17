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
 * Builds html-table for the object lists.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_list_csv extends isys_component_list
{
    /**
     * Creates the temporary table with the data from init.
     *
     * @return  boolean
     */
    public function createTempTable()
    {
        $l_header = array_filter(
            $this->m_arTableHeader,
            function ($p_val)
            {
                return !(empty($p_val) || $p_val == 'ID');
            }
        );

        $l_csv = \League\Csv\Writer::createFromFileObject(new SplTempFileObject)
            ->setDelimiter(';')
            ->addFormatter(
                function ($p_val)
                {
                    return array_map('utf8_decode', $p_val);
                }
            )
            ->insertOne(array_map('_L', array_values($l_header)));

        if ($this->m_resData)
        {
            $this->write_generic_category_rows($l_csv, $l_header);
        } // if

        // Don't use a "else" here in case a list displays two tables (see guest systems).
        if ($this->m_arData && is_array($this->m_arData) && count($this->m_arData))
        {
            // Right now this logic is optimized for custom categories.
            $this->write_custom_category_rows($l_csv, $l_header);
        } // if

        $l_csv->output($this->get_csv_filename());
        die;
    } // function

    /**
     * This method will write the contents of a generic multivalue-category to the given CSV file.
     *
     * @param  \League\Csv\Writer $p_csv
     * @param  array              $p_header
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function write_generic_category_rows(\League\Csv\Writer $p_csv, array $p_header)
    {
        $l_filter      = isys_glob_get_param('filter');
        $l_method      = $l_modify_row = $l_custom_modify_row = false;
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Exchange row-array by using method modify_row which is defined in the specific listDao.
        if ($this->m_listdao != null && is_a($this->m_listdao, "isys_component_dao_object_table_list"))
        {
            $l_method = $this->m_row_method;

            $l_modify_row = method_exists($this->m_listdao, $l_method);
        } // if

        // Custom row modifier.. (Not needed to be a table_list..)
        if (is_object($this->m_row_modifier) && method_exists($this->m_row_modifier, $this->m_row_method))
        {
            $l_method            = $this->m_row_method;
            $l_custom_modify_row = true;
        } // if

        if ($l_modify_row || $l_custom_modify_row)
        {
            $this->m_modified = true;
        } // if

        while ($l_row = $this->m_resData->get_row())
        {
            if ($l_modify_row)
            {
                $this->m_listdao->$l_method($l_row);
            } // if

            if ($l_custom_modify_row)
            {
                $this->m_row_modifier->$l_method($l_row);
            } // if

            $l_csv_row = [];

            foreach ($p_header as $l_key => $l_field)
            {
                if (isset($l_row[$l_key]))
                {
                    $l_val = $l_row[$l_key];
                }
                else
                {
                    $l_csv_row[$l_key] = $l_empty_value;
                    continue;
                } // if

                if (is_scalar($l_val))
                {
                    if (strpos($l_val, "LC") === 0)
                    {
                        $l_val = _L($l_val);
                    } // if
                }
                else if (is_array($l_val))
                {
                    $l_val = implode(PHP_EOL, $l_val);
                }
                else if (is_object($l_val) && is_a($l_val, 'isys_smarty_plugin_f'))
                {
                    /* @var  isys_smarty_plugin_f $l_val */
                    $l_val = $l_val->set_parameter('p_bEditMode', false)
                        ->set_parameter('p_editMode', false)
                        ->navigation_view(isys_application::instance()->template);
                } // if

                $l_csv_row[$l_key] = trim(strip_tags(isys_helper_textformat::remove_scripts(html_entity_decode($l_val, null, $GLOBALS['g_config']['html-encoding']))));
            } // foreach

            $l_csv_row = array_values($l_csv_row);

            if (empty($l_filter) || stripos(implode(' ', $l_csv_row), $l_filter) !== false)
            {
                $p_csv->insertOne($l_csv_row);
            } // if
        } // while
    }

    /**
     * This method will write the contents of a custom multivalue-category to the given CSV file.
     *
     * @param  \League\Csv\Writer $p_csv
     * @param  array              $p_header
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function write_custom_category_rows(League\Csv\Writer $p_csv, array $p_header)
    {
        $l_filter = isys_glob_get_param('filter');

        foreach ($this->m_arData as $l_row)
        {
            $l_csv_row = [];

            foreach ($p_header as $l_identifier => $l_field)
            {
                $l_identifier = substr($l_identifier, 5);

                $l_csv_row[] = trim(
                    strip_tags(isys_helper_textformat::remove_scripts(html_entity_decode(_L($l_row[$l_identifier]), null, $GLOBALS['g_config']['html-encoding'])))
                );
            } // foreach

            if (empty($l_filter) || stripos(implode(' ', $l_csv_row), $l_filter) !== false)
            {
                $p_csv->insertOne($l_csv_row);
            } // if
        } // foreach
    } // function

    /**
     * This method will create a CSV filename according to object and category.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function get_csv_filename()
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        if ($l_gets[C__CMDB__GET__CATG] == C__CATG__CUSTOM_FIELDS && $l_gets[C__CMDB__GET__CATG_CUSTOM] > 0)
        {
            /* @var  isys_cmdb_dao_category_g_custom_fields $l_dao */
            $l_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_custom_fields', $this->m_listdao->get_database_component());

            $l_category  = $l_dao->get_cat_custom_name_by_id_as_string($l_gets[C__CMDB__GET__CATG_CUSTOM]);
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];
        }
        else
        {
            // Try to prepare a uniform CSV filename.
            if (is_a($this->m_listdao->get_dao_category(), 'isys_cmdb_dao_category'))
            {
                $l_object_id = $this->m_listdao->get_dao_category()
                    ->get_object_id();

                switch ($this->m_listdao->get_dao_category()
                    ->get_category_type())
                {
                    default:
                    case C__CMDB__CATEGORY__TYPE_GLOBAL:
                        $l_category = $this->m_listdao->get_dao_category()
                            ->get_catg_name_by_id_as_string(
                                $this->m_listdao->get_dao_category()
                                    ->get_category_id()
                            );
                        break;

                    case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                        $l_category = $this->m_listdao->get_dao_category()
                            ->get_cats_name_by_id_as_string(
                                $this->m_listdao->get_dao_category()
                                    ->get_category_id()
                            );
                        break;

                    case C__CMDB__CATEGORY__TYPE_CUSTOM:
                        $l_category = 'TODO'; // $this->m_listdao->get_dao_category()->get_cat_custom_name_by_id_as_string($this->m_listdao->get_dao_category()->get_category_id());
                } // switch
            }
            else
            {
                if (isset($l_gets[C__CMDB__GET__CATG]))
                {
                    $l_category = $this->m_listdao->get_dao_category()
                        ->get_catg_name_by_id_as_string($l_gets[C__CMDB__GET__CATG]);
                }
                else if (isset($l_gets[C__CMDB__GET__CATS]))
                {
                    $l_category = $this->m_listdao->get_dao_category()
                        ->get_cats_name_by_id_as_string($l_gets[C__CMDB__GET__CATS]);
                }
                else
                {
                    $l_category = 'TODO';
                } // if

                $l_object_id = $l_gets[C__CMDB__GET__OBJECT];
            } // if
        } // if

        return isys_helper_textformat::clean_string(date('Y_m_d') . '__' . ((int) $l_object_id) . '__' . _L($l_category)) . '.csv';
    } // function
} // class