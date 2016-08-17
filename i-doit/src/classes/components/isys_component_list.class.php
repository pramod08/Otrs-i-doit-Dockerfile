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
 * builds html-table for the object lists.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_list
{
    /**
     *
     */
    const CL__DISABLE_ROW = 'component_list_disabled';

    /**
     * @var  array
     */
    protected $m_arData = null;

    /**
     * @var  array
     */
    protected $m_arTableColumn = [];

    /**
     * @var  array
     */
    protected $m_arTableHeader = null;

    /**
     * @var  array
     */
    protected $m_arTablecellHtml = null;

    /**
     * @var  boolean
     */
    protected $m_bOrderLink = true;

    /**
     * @var  boolean
     */
    protected $m_bOverloadCursor = false;

    /**
     * @var  boolean
     */
    protected $m_bTranslate = true;

    /**
     * @var  array
     */
    protected $m_colgroups = [];

    /**
     * @var  boolean
     */
    protected $m_dragdrop = false;

    /**
     * @var  boolean
     */
    protected $m_id = false;

    /**
     * @var isys_cmdb_dao_list
     */
    protected $m_listdao = null;

    /**
     * @var  isys_component_dao_result
     */
    protected $m_modified = false;

    /**
     * @var  integer
     */
    protected $m_nRecStatus = C__RECORD_STATUS__NORMAL;

    /**
     * @var  integer
     */
    protected $m_nRowID = 0;

    /**
     * @var  isys_component_dao_result
     */
    protected $m_resData = null;

    /**
     * @var  string
     */
    protected $m_row_formatter = "format_row";

    /**
     * @var  string
     */
    protected $m_row_method = "modify_row";

    /**
     * @var  object
     */
    protected $m_row_modifier = null;

    /**
     * @var  string
     */
    protected $m_strCheckboxValue = "";

    /**
     * @var  string
     */
    protected $m_strClass = "mainTable";

    /**
     * @var  string
     */
    protected $m_strRowLink = "";

    /**
     * @var  string
     */
    protected $m_strTempTableName = "";

    /**
     * @param null   $p_arData
     * @param null   $p_resData
     * @param null   $p_listdao
     * @param null   $p_nRecStatus
     * @param string $p_type
     *
     * @return isys_component_list_csv|isys_component_list_html
     */
    public static function factory($p_arData = null, $p_resData = null, $p_listdao = null, $p_nRecStatus = null, $p_type = 'html')
    {
        switch ($p_type)
        {
            default:
            case 'html':
                return new isys_component_list_html($p_arData, $p_resData, $p_listdao, $p_nRecStatus);

            case 'csv':
                return new isys_component_list_csv($p_arData, $p_resData, $p_listdao, $p_nRecStatus);
        } // switch
    } // function

    /**
     * Method for activating the drag'n'drop feature.
     *
     * @return  isys_component_list
     */
    public function enable_dragndrop()
    {
        $this->m_dragdrop = true;

        return $this;
    } // function

    /**
     * Method for deactivating the drag'n'drop feature.
     *
     * @return  isys_component_list
     */
    public function disable_dragndrop()
    {
        $this->m_dragdrop = false;

        return $this;
    } // function

    /**
     * Sets a custom row modifier which is called when generating the list and has to have
     * the method modify_row(inout $p_row) implemented
     *
     * @param   object  $p_object
     * @param   boolean $p_method
     *
     * @return  isys_component_list
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
     * Method for setting a list DAO.
     *
     * @param  $p_listdao
     */
    public function set_listdao($p_listdao)
    {
        $this->m_listdao = $p_listdao;
    }

    /**
     * Creates the temporary table with the data from init.
     *
     * @param   integer $p_int_num_row
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function createTempTable(&$p_int_num_row = 0)
    {
        global $g_comp_database;

        $l_strSQLTemp = "";
        $l_objDAO     = new isys_component_dao($g_comp_database);

        $this->m_id = "isys_id";

        if (!array_key_exists($this->m_id, $this->m_arTableHeader) && !array_key_exists("id", $this->m_arTableHeader))
        {
            $this->m_arTableHeader[$this->m_id] = "id";
        } // if

        $l_header = $this->m_arTableHeader;

        $l_objDAO->begin_update();

        // Work with a DAO result.
        if ($this->m_resData)
        {
            if (($l_row_link_value = $this->getRowLinkValue($this->m_strRowLink)))
            {
                $l_header[$l_row_link_value] = "ID";
            } // if

            $l_numheader = count($l_header);

            // Read name, type and length for every field.
            foreach ($l_header as $l_field => $l_field_text)
            {
                // Write column name to member var.
                $this->m_arTableColumn[] = $l_field;

                if (strstr($l_field, "__id") || $l_field == "object_id")
                {
                    $l_type = "INT(10)";
                }
                else
                {
                    $l_type = "TEXT";
                } // if

                if (substr($l_field, 0, 4) != "isys")
                {
                    $l_strSQLTemp .= "isys_" . $l_field . " " . $l_type . ",";
                }
                else
                {
                    $l_strSQLTemp .= $l_field . " " . $l_type . ",";
                } // if
            } // foreach

            $l_strSQLTemp = rtrim($l_strSQLTemp, ",");
        }
        else
        {
            $l_nArrayLength = count($this->m_arData[0]);
            $i              = 0;

            if (is_array($this->m_arData[0]))
            {
                foreach ($this->m_arData[0] as $key => $value)
                {
                    $l_type = "LONGTEXT";

                    if (substr($key, 0, 4) != "isys")
                    {
                        $l_strSQLTemp .= "isys_" . $key . " " . $l_type;
                        $this->m_arTableColumn[] = "isys_" . $key;
                    }
                    else
                    {
                        $l_strSQLTemp .= "" . $key . " " . $l_type;
                        $this->m_arTableColumn[] = $key;
                    } // if

                    if ($i + 1 < $l_nArrayLength)
                    {
                        $l_strSQLTemp .= ", ";
                    } // if

                    $i++;
                } // foreach
            } // if
        } // if

        $l_tempTableName = isys_glob_get_obj_list_table_name();

        // Save new name.
        $this->m_strTempTableName = $l_tempTableName;

        // Drop temporary table.
        $l_strSQL          = "DROP TABLE IF EXISTS {$l_tempTableName};";
        $l_bEverythingGood = $l_objDAO->update($l_strSQL);

        if (strlen($l_strSQLTemp) < 3)
        {
            return false;
        } // if

        // Create temporary table.
        if ($l_bEverythingGood)
        {
            $l_strSQL          = "CREATE TEMPORARY TABLE {$l_tempTableName} (" . $l_strSQLTemp . ") ENGINE=MyISAM;";
            $l_bEverythingGood = $l_objDAO->update($l_strSQL);
        } // if

        if ($l_bEverythingGood)
        {
            // Now insert the data from the old result/array into the new table.

            // Array to split up the SQL-Statement into smaller Queries, otherwise max_packet may be exceeded, when object lists are very large.
            $l_queries = [];

            if ($this->m_resData)
            {
                $l_strSQLTemp = "";
                $l_currentrow = 0;

                // Row count wrong with ports!? there is a "group by" in the result set! so the num_rows() doesnt seem to work.
                $l_rows = $this->m_resData->num_rows();

                $l_method = $l_modify_row = $l_custom_modify_row = false;

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

                while ($l_row_set = $this->m_resData->get_row())
                {
                    $l_currentrow++;

                    if ($l_modify_row)
                    {
                        $this->m_listdao->$l_method($l_row_set);
                    } // if

                    if ($l_custom_modify_row)
                    {
                        $this->m_row_modifier->$l_method($l_row_set);
                    } // if

                    $i = 1;

                    foreach ($l_header as $l_key => $l_field_text)
                    {
                        if ($l_key == "isys_id")
                        {
                            $l_row = $l_row_set[substr(substr($this->m_strCheckboxValue, 2), 0, strlen($this->m_strCheckboxValue) - 4)];
                        }
                        else
                        {
                            $l_row = $l_row_set[$l_key];

                            if (is_scalar($l_row))
                            {
                                if (strpos($l_row, "LC") === 0)
                                {
                                    $l_row = _L($l_row);
                                } // if
                            }
                            else
                            {
                                if (is_array($l_row))
                                {
                                    $l_row = '<ul class="list-style-non"><li>' . implode('</li><li>', $l_row) . '</li></ul>';
                                }
                                else
                                {
                                    if (is_object($l_row) && is_a($l_row, 'isys_smarty_plugin_f'))
                                    {
                                        $l_row = $l_row->set_parameter('p_bEditMode', true)
                                            ->set_parameter('p_editMode', true)
                                            ->navigation_edit(isys_application::instance()->template);
                                    }
                                }
                            } // if
                        } // if

                        $l_strSQLTemp .= "'" . $g_comp_database->escape_string($l_row) . "'";

                        if ($i++ < $l_numheader)
                        {
                            $l_strSQLTemp .= ',';
                        } // if
                    } // foreach

                    // Split up the query per 250 entries.
                    if (($l_currentrow % 250) == 0)
                    {
                        $l_queries[] = $l_strSQLTemp;
                        unset($l_strSQLTemp);
                    }
                    else
                    {
                        if ($l_currentrow < $l_rows)
                        {
                            $l_strSQLTemp .= "), (";
                        }
                    }// if
                } // while

                $this->m_resData->free_result();

                // Add the remaining entries.
                if (!empty($l_strSQLTemp))
                {
                    $l_queries[] = $l_strSQLTemp;
                } // if
            }
            else
            {
                $l_strSQLTemp = "";
                $l_currentrow = 0;
                $l_rows       = count($this->m_arData);

                foreach ($this->m_arData as $l_arVal)
                {
                    $l_currentrow++;
                    $l_nTableFields = count($l_arVal);
                    $i              = 1;

                    foreach ($l_arVal as $val)
                    {
                        // If its a language constant, translate it.
                        if (stripos($val, "LC") === 0)
                        {
                            $val = _L($val);
                        } // if

                        if ($val == null)
                        {
                            $l_strSQLTemp .= "NULL";
                        }
                        else
                        {
                            $l_strSQLTemp .= "'" . $g_comp_database->escape_string($val) . "'";
                        } // if

                        if ($i < $l_nTableFields)
                        {
                            $l_strSQLTemp .= ", ";
                        } // if

                        $i++;
                    } // foreach

                    // Split up the query per 250 entries.
                    if (($l_currentrow % 250) == 0)
                    {
                        $l_queries[]  = $l_strSQLTemp;
                        $l_strSQLTemp = "";
                    }
                    else
                    {
                        if ($l_currentrow != $l_rows)
                        {
                            $l_strSQLTemp .= "), (";
                        }
                    } // if
                } // foreach

                // Add the remaining entries.
                if (!empty($l_strSQLTemp))
                {
                    $l_queries[] = $l_strSQLTemp;
                } // if
            } // if

            if (count($l_queries) > 0)
            {
                foreach ($l_queries as $l_sub)
                {
                    $l_bEverythingGood = $l_objDAO->update('INSERT INTO ' . $l_tempTableName . ' VALUES (' . $l_sub . ');');

                    if (!$l_bEverythingGood)
                    {
                        break;
                    } // if
                } // foreach
            }
            else
            {
                $l_bEverythingGood = true;
            } // if
        }
        else
        {
            echo "Error: $l_strSQL<br />";
        } // if

        $l_bRet = $l_bEverythingGood;

        // Clear variable register.
        unset($l_strSQL, $l_bEverythingGood, $l_queries, $l_objDAO, $l_strSQLTemp, $l_row);

        return $l_bRet;
    } // function

    /**
     * Beware that the keys in m_arTableHeader have to be a column name from the temporary table, or you have to leave them empty.
     *
     * @param   array   $p_arTableHeader
     * @param   string  $p_strRowLink
     * @param   string  $p_strCheckboxValue
     * @param   boolean $p_bTranslate
     * @param   boolean $p_bOrderLink
     * @param   array   $p_colgroups
     * @param   boolean $p_bOverloadCursor To overload cursor ie no link, see @tables.css CMDBListElementsOdd cursor:pointer;
     *
     * @return  isys_component_list
     * @throws  Exception
     */
    public function config($p_arTableHeader, $p_strRowLink = "", $p_strCheckboxValue = "", $p_bTranslate = true, $p_bOrderLink = true, $p_colgroups = null, $p_bOverloadCursor = false)
    {
        if (!is_array($p_arTableHeader))
        {
            throw new Exception("Table headers are required to create a list.");
        }
        else
        {
            $this->m_arTableHeader = $p_arTableHeader;
        } // if

        if (!empty($p_strRowLink))
        {
            $this->m_strRowLink = $p_strRowLink;
        } // if

        if ($p_strCheckboxValue != "")
        {
            $this->m_strCheckboxValue = $p_strCheckboxValue;
        } // if

        if ($p_bTranslate == false)
        {
            $this->m_bTranslate = $p_bTranslate;
        } // if

        if (!$p_bOrderLink)
        {
            $this->m_bOrderLink = false;
        } // if

        if (!is_null($p_colgroups))
        {
            $this->m_colgroups = $p_colgroups;
        } // if

        if ($p_bOverloadCursor)
        {
            $this->m_bOverloadCursor = true;
        } // if

        return $this;
    } // function

    /**
     * Set the class for the html table.
     *
     * @param   string $p_strClass
     *
     * @return  isys_component_list
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function setTableClass($p_strClass)
    {
        if (!empty($p_strClass))
        {
            $this->m_strClass = $p_strClass;
        } // if

        return $this;
    } // function

    /**
     * Sets the links for the table cells with a multidimensional array.
     *
     * @param   array $p_arTablecellHtml
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function setTablecellHtml($p_arTablecellHtml)
    {
        if (is_array($p_arTablecellHtml))
        {
            $this->m_arTablecellHtml = $p_arTablecellHtml;
        } // if
    }

    /**
     * Returns the name of the temporary table from which the html table is created.
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function getTempTableName()
    {
        return $this->m_strTempTableName;
    } // function

    /**
     * Return the html table.
     *
     * @param   mixed   $p_dao_result
     * @param   boolean $p_translate
     *
     * @return  string
     * @throws  isys_exception_general
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function getTempTableHtml($p_dao_result = null, $p_translate = false)
    {
        global $g_comp_template_language_manager, $g_comp_template, $g_ajax_calls;

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_strRet              = "";
        $l_nRowCounter         = 0;
        $l_objTemplate         = $g_comp_template;
        $l_selected_checkboxes = json_decode($_POST['savedCheckboxes']);
        $l_selected_checkboxes = (!is_array($l_selected_checkboxes)) ? [] : $l_selected_checkboxes;

        if ($this->m_id)
        {
            unset($this->m_arTableHeader[$this->m_id]);
        } // if

        if (is_null($p_dao_result))
        {
            $l_objDAORes = $this->getTableResult($this->m_strTempTableName);
        }
        else
        {
            $l_objDAORes = $p_dao_result;
        } // if

        $l_bCheckbox  = false;
        $l_bOrderLink = $this->m_bOrderLink;

        if (!$l_objDAORes)
        {
            throw new isys_exception_general(get_class($this) . ": getTempTableHtml doesn't have any results.");
        } // if

        $l_row = $this->m_listdao->retrieve("SELECT FOUND_ROWS() AS ROWS_ALL;")
            ->__to_array();
        if ($l_row["ROWS_ALL"] > 0)
        {
            $l_num_rows = $l_row["ROWS_ALL"];
            unset($l_row);
        }
        else
        {
            if (is_object($l_objDAORes))
            {
                $l_num_rows = $l_objDAORes->num_rows();
            }
            else $l_num_rows = 0;
        }

        $l_navbar->set_nav_page_count($l_num_rows);

        //is there a checkbox?
        if ($this->m_strCheckboxValue != "") $l_bCheckbox = true;

        $l_strTooltip         = $g_comp_template_language_manager->{"LC__UNIVERSAL__SORT"};
        $l_strCheckboxValue   = '';
        $l_init_drag          = false;
        $l_dragclass          = '';
        $l_checkbox_container = $this->m_strTempTableName . "__save";

        $l_strRet .= "<script type='text/javascript'>if (typeof " . $l_checkbox_container . " == 'undefined') \n{" . $l_checkbox_container . " = [];}</script>";
        $l_strRet .= "<input type='hidden' name='savedCheckboxes' id='savedCheckboxes' value='" . json_encode(
                $l_selected_checkboxes
            ) . "' data-container='{$l_checkbox_container}'/>";
        $l_strRet .= "<table id=\"" . $this->m_strClass . "\" cellspacing=\"0\" class=\"$this->m_strClass\">";

        if ($l_bCheckbox)
        {
            array_unshift($this->m_colgroups, "20");
        }

        if (is_array($this->m_colgroups) && count($this->m_colgroups) > 0)
        {

            $l_strRet .= "<colgroup>";
            foreach ($this->m_colgroups as $l_col)
            {
                $l_strRet .= "<col width=\"" . $l_col . "px\" />";
            }
        }

        if ($l_bCheckbox || count($this->m_colgroups) > 0) $l_strRet .= "</colgroup>";

        if ($this->m_arTableHeader && ($l_num_rows != 0))
        {
            //build table header
            $l_strRet .= "<tr>";
            $i = 0;

            // w/ or w/o checkbox
            if ($l_bCheckbox)
            {
                $l_strRet .= '<th><input type="checkbox" onClick="CheckAllBoxes(this);" value="X" /></th>';
            }

            if (!$g_ajax_calls)
            {
                $l_submit = "document.isys_form.submit();";
            }
            else
            {
                $l_submit = "form_submit();";
            }

            $l_tmp_table = "";

            foreach ($this->m_arTableHeader as $key => $value)
            {

                if ($value === false)
                {
                    /* Header valued with FALSE will be unsetted */
                    unset($this->m_arTableHeader[$key]);
                    continue;
                }

                $i++;
                $value = $g_comp_template_language_manager->{$value};

                //sort by $key
                if ($this->isTableColumn($key))
                {
                    if ($l_bOrderLink)
                    {

                        $l_onclick = $l_tmp_table . "document.isys_form.savedCheckboxes.value = " . "JSON.stringify($l_checkbox_container);" .
                            "document.isys_form.dir.value=" . "'" . isys_glob_get_order() . "';" . "document.isys_form.sort.value=" . "'" . $key . "';" .
                            "document.isys_form.navMode.value='';" . "remove_action_parameter('id');" . $l_submit;

                        $l_dir  = isys_glob_get_param("dir");
                        $l_sort = isys_glob_get_param("sort");

                        $l_strRet .= '<th title="' . $l_strTooltip . '"><a href="javascript:" onclick="' . $l_onclick . '">';

                        if ($l_sort == $key)
                        {
                            global $g_dirs;

                            $l_strRet .= '<img class="fr" style="margin-top:8px;margin-right:-2px;" src="' . $g_dirs["images"] . '/list/' . strtolower($l_dir) . '.gif" />';
                        }

                        $l_strRet .= $value . "</a></th>";
                    }
                    else
                    {
                        $l_strRet .= "<th>$value</th>";
                    }
                }
                else
                {
                    $l_strRet .= "<th>$value</th>";
                }
            }
            $l_strRet .= "</tr>";
        }

        //is there at least one row?
        if ($l_num_rows == 0)
        {
            //get record status
            $l_strLC = "";

            switch ($this->m_nRecStatus)
            {
                case C__RECORD_STATUS__ARCHIVED:
                    $l_strLC = "LC__CMDB__RECORD_STATUS__ARCHIVED";
                    break;
                case C__RECORD_STATUS__BIRTH:
                    $l_strLC = "LC__CMDB__RECORD_STATUS__BIRTH";
                    break;
                case C__RECORD_STATUS__DELETED:
                    $l_strLC = "LC__CMDB__RECORD_STATUS__DELETED";
                    break;
                case C__RECORD_STATUS__NORMAL:
                    $l_strLC = "LC__CMDB__RECORD_STATUS__NORMAL";
                    break;
                case C__RECORD_STATUS__PURGE:
                    $l_strLC = "LC__CMDB__RECORD_STATUS__PURGE";
                    break;
                case C__RECORD_STATUS__TEMPLATE:
                    $l_strLC = "Template";
                    break;
            }

            if (strlen($l_strLC) > 1 && $_GET[C__CMDB__GET__VIEWMODE] != C__WF__VIEW__LIST)
            {
                $l_strRecordStatus = _L($l_strLC);
                $l_strTemp         = _L("LC__CMDB__FILTER__NOTHING_FOUND", ["var1" => $l_strRecordStatus]);
            }
            else
            {
                $l_strTemp = _L("LC__CMDB__FILTER__NOTHING_FOUND_STD");
            } // if

            $l_strRet = '<div class="p10">' . $l_strTemp . '</div>';

            $l_navbar->set_visible(false, C__NAVBAR_BUTTON__PRINT);

            array_map(
                function ($l_item) use ($l_navbar)
                {
                    if ($l_navbar->is_active($l_item))
                    {
                        $l_navbar->set_active(false, $l_item);
                    } // if
                },
                [
                    C__NAVBAR_BUTTON__EDIT,
                    C__NAVBAR_BUTTON__ARCHIVE,
                    C__NAVBAR_BUTTON__EXPORT_AS_CSV,
                    C__NAVBAR_BUTTON__ARCHIVE,
                    C__NAVBAR_BUTTON__DELETE,
                    C__NAVBAR_BUTTON__PURGE,
                    C__NAVBAR_BUTTON__QUICK_PURGE
                ]
            );
        }
        else
        {
            if ($this->m_dragdrop && defined("C__OBJECT_DRAGNDROP") && C__OBJECT_DRAGNDROP)
            {
                $l_dragclass = " draggable";
                $l_init_drag = true;
            }

            $l_modify_row        = false;
            $l_custom_modify_row = false;
            $l_format_row        = false;

            if (!$this->m_modified)
            {
                // exchange row-array by using method modify_row
                // which is defined in the specific listDao
                if ($this->m_listdao != null)
                {
                    if (is_a($this->m_listdao, "isys_component_dao_object_table_list"))
                    {
                        $l_method = $this->m_row_method;
                        if (method_exists($this->m_listdao, $l_method)) $l_modify_row = true;
                    }
                }

                /**
                 * Custom row modifier.. (Not needed to be a table_list..)
                 *
                 * @author Dennis Stuecken
                 */
                if (is_object($this->m_row_modifier))
                {
                    if (method_exists($this->m_row_modifier, $this->m_row_method))
                    {
                        $l_method            = $this->m_row_method;
                        $l_custom_modify_row = true;
                    }
                }
            }

            if ($this->m_listdao != null)
            {
                if (is_a($this->m_listdao, "isys_component_dao_object_table_list"))
                {
                    $l_formatter_method = $this->m_row_formatter;
                    if (method_exists($this->m_listdao, $l_formatter_method)) $l_format_row = true;
                }
            }

            while ($l_row = $l_objDAORes->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY))
            {

                foreach ($l_row as $l_key => $l_value)
                {
                    $l_row[substr($l_key, 5)] = $l_value;
                }

                if ($l_modify_row) $this->m_listdao->$l_method($l_row);

                if ($l_custom_modify_row) $this->m_row_modifier->$l_method($l_row);

                if ($l_format_row) $this->m_listdao->$l_formatter_method($l_row);

                //build table row
                if (!empty($this->m_strRowLink))
                {

                    if ($g_ajax_calls && strstr(htmlspecialchars_decode($this->m_strRowLink), ";"))
                    {
                        $l_strRowLink = $this->m_strRowLink;
                    }
                    else
                    {
                        $this->m_strRowLink = str_replace("&ajax=1", "", $this->m_strRowLink);

                        //search and replace VARS in link
                        $l_strRowLink = "document.location.href='" . $this->m_strRowLink . "';";
                    }
                    //replace values in [{...}] with row content
                    $this->replaceLinkValues($l_strRowLink, $l_row);
                }
                else
                {
                    $l_strRowLink = "";
                }

                if ($l_strRowLink)
                {
                    $l_onclick = $l_strRowLink;
                }
                else
                {
                    $l_onclick = "";
                }

                if ($l_bCheckbox)
                {
                    //search and replace VARS in checkbox string
                    $l_strCheckboxValue = $this->m_strCheckboxValue;
                    $l_strCheckboxValue = str_replace($l_strCheckboxValue, $l_row[substr($l_strCheckboxValue, 2, -2)], $l_strCheckboxValue);
                    if (empty($l_strCheckboxValue)) $l_strCheckboxValue = $l_row["isys_id"];
                }

                //table row with highlighting
                $l_strRet .= "<tr " . "id=\"tablerow_" . $l_strCheckboxValue . "\" " . "class=\"listRow " . $l_objTemplate->row_background_color(
                        $l_nRowCounter
                    ) . $l_dragclass . "\"";

                // overload css
                if ($this->m_bOverloadCursor)
                {
                    $l_strRet .= "style=\"cursor:auto;\">";
                }
                else
                {
                    $l_strRet .= ">";
                }

                if ($l_bCheckbox)
                {
                    $l_disabled = "";
                    if (isset($l_row[self::CL__DISABLE_ROW]) && $l_row[self::CL__DISABLE_ROW])
                    {
                        $l_disabled = " disabled='disabled' ";
                        $l_onclick  = "";
                    }
                    if ((in_array($l_strCheckboxValue, $l_selected_checkboxes)))
                    {
                        $l_strCheckedStatus = "checked='checked'";
                    }
                    else
                    {
                        $l_strCheckedStatus = "";
                    }

                    $l_strRet .= "<td>" . "<input type=\"checkbox\" " . $l_disabled . " class=\"checkbox\" name=\"id[]\" value=\"" . $l_strCheckboxValue . "\" " .
                        $l_strCheckedStatus . " " . "onClick=\"
                                        if (this.checked) {
                                            $l_checkbox_container.push(this.value)
                                        } else {
                                            $l_checkbox_container.splice($l_checkbox_container.indexOf(this.value),1);
                                        }document.isys_form.savedCheckboxes.value = JSON.stringify($l_checkbox_container);\"/>" . "</td>";
                }

                foreach ($this->m_arTableHeader as $key => $value)
                {

                    $l_strTablecellContent = @$l_row[$key];

                    if (is_array($this->m_arTablecellHtml))
                    {
                        //if a key from the array m_arTablecellHtml matches the
                        // current key from m_arTableHeader then switch the content
                        // of the table cell with the value from m_arTablecellHtml
                        if (key_exists($key, $this->m_arTablecellHtml))
                        {
                            $l_strTablecellContent = $this->m_arTablecellHtml[$key];
                            //now parse the content for "[{...}]"
                            $this->replaceLinkValues($l_strTablecellContent, $l_row);
                        }
                    }

                    if ($this->m_dragdrop)
                    {
                        $l_strRet .= "<td onclick=\"if(!dragging){" . $l_onclick . "}else{return false;}\">";
                    }
                    else
                    {
                        $l_strRet .= "<td onclick=\"" . $l_onclick . "\">";
                    }

                    $l_strRet .= $l_strTablecellContent . "</td>";
                }
                $l_strRet .= "</tr>";

                $l_nRowCounter++;
            }
            $l_objDAORes->free_result();
        }

        $l_strRet .= "</table>";

        if ($l_init_drag)
        {
            $l_strRet .= "<script type=\"text/javascript\">" . "Position.includeScrollOffsets = true;" . "$$('.mainTable tr.listRow').each(function(tr){ " . "drags.push(" .
                "new Draggable(tr, " . "{ " . "sourceElement: 'mainTable', " . "revert: true, " . "ghosting: true, " . "onStart:function(el){dragging=true;
														//document.body.appendChild(el.element);
													}," . "onDrag: function(el){}, " . "onEnd:function(el){window.setTimeout('dragging=false', 800);
														//$('mainTable').appendChild(el.element);
													}" . "}" . ")" . ");\n" . "});" . "</script>";
        }

        $g_comp_template->assign("list_display", true);

        if (method_exists($this->m_listdao, 'rec_status_list_active'))
        {
            $g_comp_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=" . ($this->m_listdao->rec_status_list_active() ? "0" : "1"));
        } // if

        return $l_strRet;
    } // function

    /**
     *
     * @param   array                     $p_arData
     * @param   isys_component_dao_result $p_resData
     *
     * @return  isys_component_list
     */
    public function set_data($p_arData = null, $p_resData = null)
    {
        if (is_array($p_arData))
        {
            $this->m_arData = $p_arData;
        }
        else
        {
            $this->m_resData = $p_resData;
        } // if

        return $this;
    } // function

    /**
     *
     * @param   array $p_arData
     *
     * @return  isys_component_list
     */
    public function set_m_arTableColumn($p_arData = null)
    {
        if (is_array($p_arData))
        {
            foreach ($p_arData AS $l_key => $l_val)
            {
                $this->m_arTableColumn[] = is_numeric($l_key) ? $l_val : $l_key;
            } // foreach
        } // if

        return $this;
    } // function

    /**
     * @param string $p_strString
     * @param array  $p_arRow
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     * @desc   searches for [{...}] in strings and replaces them with the value
     *         of the row of the DAO result.
     *         the maximal count of values to be translated is 10.
     */
    protected function replaceLinkValues(&$p_strString, $p_arRow, $p_max_count = 10)
    {
        $i = 0;
        while (preg_match("/\[\{(.*?)\}\]/i", $p_strString, $l_reg))
        {
            if (isset($p_arRow[$l_reg[1]]))
            {
                $p_strString = str_replace("[{" . $l_reg[1] . "}]", $p_arRow[$l_reg[1]], $p_strString);
            }
            if (++$i == $p_max_count) break;
        }
    } // function

    /**
     * @param $p_strString
     *
     * @return bool
     */
    protected function getRowLinkValue(&$p_strString)
    {
        if (preg_match("/\[\{(.*?)\}\]/i", $p_strString, $l_reg))
        {
            return $l_reg[1];
        }
        else
        {
            return false;
        } // if
    }

    /**
     * Checks whether a string is a column name from the temp table.
     *
     * @param   string $p_strName
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function isTableColumn($p_strName)
    {
        return in_array($p_strName, $this->m_arTableColumn);
    } // function

    /**
     * @global $g_comp_database
     * @global $g_page_limit
     *
     * @return isys_component_dao_result
     * @author Niclas Potthast <npotthast@i-doit.de> - 2005-11-29
     * @author Selcuk Kekec <skekec@i-doit.de> - 2013-02-12
     * @desc   remember that certain elements in the given result set have to
     *         be already filtered. for example "cRecStatus".
     */
    protected function getTableResult($p_tableName)
    {
        global $g_comp_database;

        $l_strTableName = $p_tableName;
        $l_strFilter    = isys_glob_get_param("filter");

        $l_strFilter = $g_comp_database->escape_string($l_strFilter);

        if (empty($l_strTableName))
        {
            return null;
        }

        $l_strSQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $l_strTableName ";

        //use the filter if it's set
        if ($l_strFilter)
        {
            $l_nCount = 0;
            $l_strSQL .= "WHERE ";

            foreach ($this->m_arTableHeader as $key => $value)
            {
                //only use the filter if its a real column
                if ($this->isTableColumn($key))
                {
                    if ($l_nCount >= 1)
                    {
                        $l_strSQL .= " OR ";
                    }
                    if (substr($key, 0, 4) != "isys" && $this->m_resData)
                    {
                        $l_strSQL .= "isys_" . $key . " LIKE '%" . $l_strFilter . "%' ";
                    }
                    else
                    {
                        $l_strSQL .= $key . " LIKE '%" . $l_strFilter . "%' ";
                    }
                    $l_nCount++;
                }
            }
        }

        //use the sorting if it's set
        $l_arTemp = (is_array($this->m_arTableHeader) ? array_keys($this->m_arTableHeader) : []);
        $l_dir    = (strlen(isys_glob_get_param("dir")) > 0) ? isys_glob_get_param("dir") : 'ASC';
        $l_sort   = (strlen(isys_glob_get_param("sort")) > 0) ? isys_glob_get_param("sort") : $l_arTemp[0];

        if (strlen($l_dir) > 0)
        {
            if (strlen($l_sort) > 0)
            {
                if ($this->isTableColumn($l_sort))
                {
                    if (substr($l_sort, 0, 4) != "isys" && $this->m_resData)
                    {
                        $l_sort = "isys_" . $l_sort;
                    }

                    $l_strSQL .= "ORDER BY ";

                    if (method_exists($this->m_listdao, "get_order_condition"))
                    {
                        $l_strSQL .= $this->m_listdao->get_order_condition($l_sort, $l_dir);
                    }
                    else
                    {
                        $l_strSQL .= $l_sort . " " . $l_dir;
                    }
                }
            }
        }

        // Limit the result
        if (isys_glob_get_param("navPageStart"))
        {
            $l_strSQL = $g_comp_database->limit_query($l_strSQL, isys_glob_get_pagelimit(), isys_glob_get_param("navPageStart"));
        }
        else
        {
            $l_strSQL = $g_comp_database->limit_query($l_strSQL, isys_glob_get_pagelimit(), 0);
        }

        return $this->m_listdao->retrieve($l_strSQL);
    } // function

    /**
     *
     * @param   array                     $p_arData
     * @param   isys_component_dao_result $p_resData
     * @param   isys_cmdb_dao             $p_listdao
     * @param   integer                   $p_nRecStatus
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function __construct($p_arData = null, $p_resData = null, $p_listdao = null, $p_nRecStatus = null)
    {
        global $g_comp_database;

        if (!is_object($p_listdao))
        {
            $this->set_listdao(new isys_cmdb_dao($g_comp_database));
        }
        else
        {
            $this->set_listdao($p_listdao);
        } // if

        if (is_array($p_arData))
        {
            $this->m_arData = $p_arData;
        }
        else
        {
            $this->m_resData = $p_resData;
        } // if

        if ($p_nRecStatus)
        {
            $this->m_nRecStatus = $p_nRecStatus;
        }
        else
        {
            $this->m_nRecStatus = $_SESSION["cRecStatusListView"];
        } // if
    } // function
} // class