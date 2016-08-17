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
 * CMDB UI: Category abstraction layer used by isys_cmdb_ui_category_*
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/licensee
 */
abstract class isys_cmdb_ui_category
{
    /**
     * Assigned category-DAO.
     *
     * @var  isys_cmdb_dao_category
     */
    protected $m_catdao;
    /**
     * @var integer
     */
    protected $m_category_data_id = null;
    /**
     * Member variable to switch-off the "Export as CSV" button.
     *
     * @var  boolean
     */
    protected $m_csv_export = true;
    /**
     * Holds the database-component.
     *
     * @var  isys_component_database
     */
    protected $m_database_component;
    /**
     * @var integer
     */
    protected $m_object_id = null;
    /**
     * Template component.
     *
     * @var  isys_component_template
     */
    protected $m_template;
    /**
     * Holds the template-file.
     *
     * @var  string
     */
    protected $m_template_file;

    /**
     * Fetches category's title from database.
     *
     * @param   isys_cmdb_dao_category &$p_cat Category's DAO
     *
     * @return  string  Category's title, otherwise error message.
     * @author  Andrue Wuesten <awoesten@i-doit.org>
     */
    abstract public function gui_get_title(isys_cmdb_dao_category &$p_cat); // function

    /**
     * Enables the ajax save mechanism. This is usually called in isys_cmdb_view_category.
     *
     * @param   string $p_ajax_return_element
     *
     * @author  Dennis Stuecken <dstuecekn@i-doit.org>
     */
    public static function enable_ajax_save($p_ajax_return_element = 'ajaxReturnNote')
    {
        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return($p_ajax_return_element);
    } // function

    /**
     * @param   integer $p_id
     *
     * @return  $this
     */
    public function set_object_id($p_id)
    {
        $this->m_object_id = $p_id;

        return $this;
    } // function

    /**
     * @param   integer $p_id
     *
     * @return  $this
     */
    public function set_category_data_id($p_id)
    {
        $this->m_category_data_id = $p_id;

        return $this;
    } // function

    /**
     * Build the detail category view with the template.
     *
     * @param       isys_cmdb_dao_category & $p_cat
     *
     * @return      isys_cmdb_ui_category
     * @author      Niclas Potthast <npotthast@i-doit.org>
     * @deprecated  Please use "activate_commentary()" instead.
     */
    public function detail_view(&$p_cat)
    {
        return $this->activate_commentary($p_cat);
    } // function

    /**
     * Method for activating the commentary field inside a category.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  isys_cmdb_ui_category
     * @todo    Once all UI classes are updated, we don't need the parameter anymore and can simply use "$this".
     */
    public function activate_commentary(&$p_cat)
    {
        if ($p_cat instanceof isys_cmdb_dao_category && $p_cat->get_category_id() != C__CATG__OVERVIEW)
        {
            $this->get_template_component()
                ->assign('bShowCommentary', true);
        } // if

        return $this;
    } // function

    /**
     * Deactivate commentary field below category template.
     *
     * @return  isys_cmdb_ui_category
     */
    public function deactivate_commentary()
    {
        $this->get_template_component()
            ->assign('bShowCommentary', false);

        return $this;
    } // function

    /**
     * Disables the ajax save mechanism, so you can disable it if a category does not work with ajax saving (like the object image upload).
     *
     * @return  isys_cmdb_ui_category
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function disable_ajax_save()
    {
        if (isys_tenantsettings::get('cmdb.registry.quicksave', 1))
        {
            isys_component_template_navbar::getInstance()
                ->set_save_mode('formsubmit');
        }
        else
        {
            // @todo  Maybe "log_formsubmit" or something?
            isys_component_template_navbar::getInstance()
                ->set_save_mode('log');
        } // if

        return $this;
    } // function

    /**
     * This method will try to fill the GUI formfields with the help of the properties.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   array                  $p_rules
     * @param   array                  $p_catdata
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function fill_formfields($p_cat, &$p_rules, $p_catdata)
    {
        if ($p_cat instanceof isys_cmdb_dao_category)
        {
            $l_props = $p_cat->get_properties(C__PROPERTY__WITH__VALIDATION);

            $l_request = isys_request::factory()
                ->set_object_id($p_cat->get_object_id())
                ->set_category_data_id($p_cat->get_list_id())
                ->set_category_type($p_cat->get_category_type())
                ->set_object_type_id($p_cat->get_objTypeID($p_cat->get_object_id()));

            if (is_array($p_catdata))
            {
                $l_request->set_row($p_catdata);
            }

            foreach ($l_props as $l_prop)
            {
                if (!isset($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID])) continue;

                if (is_array($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]))
                {
                    foreach ($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] as $l_key => $l_value)
                    {
                        // @todo  This can be removed once we separated the validation preparation from the default "process" method.
                        if ($l_key == 'p_strClass')
                        {
                            $l_validation = ($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] || $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] || $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] || $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL]);

                            if (isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) && $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY])
                            {
                                $l_value .= ' validate-mandatory';
                            } // if

                            if ($l_validation || count($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION]))
                            {
                                $l_value .= ' validate-rule';
                            } // if
                        } // if

                        if (is_object($l_value) && is_a($l_value, 'isys_callback'))
                        {
                            $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key] = $l_value->execute($l_request);
                        }
                        else
                        {
                            $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key] = $l_value;
                        } // if
                    } // foreach
                } // if

                switch ($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__TYPE])
                {
                    case C__PROPERTY__UI__TYPE__DIALOG:
                    case C__PROPERTY__UI__TYPE__POPUP:
                        if (!array_key_exists('p_strSelectedID', $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]))
                        {
                            $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strSelectedID'] = $p_catdata[$l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        }
                        if (isset($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) && empty($p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strSelectedID']))
                        {
                            // Only set the "default" value, if we are not handling a dialog with the value "0".
                            if (!($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG && ($p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strSelectedID'] === '0' || $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strSelectedID'] === 0)))
                            {
                                $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strSelectedID'] = $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT];
                            }
                        }
                        break;

                    case C__PROPERTY__UI__TYPE__CHECKBOX:
                        $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_bChecked'] = (bool) $p_catdata[$l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        break;

                    default:
                        if (!isset($p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strValue']))
                        {
                            $p_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strValue'] = $p_catdata[$l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        }
                        break;
                } // switch
            } // foreach
        } // if

        return $this;
    } // if

    /**
     * Returns the database component.
     *
     * @return  isys_component_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_database_component()
    {
        return $this->m_database_component;
    } // function

    /**
     * Sets the template file (*.tpl).
     *
     * @param   string $p_template
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_template($p_template)
    {
        if (!strstr($p_template, '/'))
        {
            $this->m_template_file = 'content/bottom/content/' . $p_template;
        }
        else
        {
            $this->m_template_file = $p_template;
        } // if

        return $this;
    } // function

    /**
     * Sets edit mode.
     *
     * @param   integer $p_editmode
     *
     * @return  isys_cmdb_ui_category
     * @throws  Exception
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_editmode($p_editmode)
    {
        if ($p_editmode !== C__EDITMODE__ON && $p_editmode !== C__EDITMODE__OFF)
        {
            throw new Exception('Edit mode is not set properly! Expected: (integer) 0 or 1.');
        } // if

        $l_gets                         = isys_module_request::get_instance()
            ->get_gets();
        $l_gets[C__CMDB__GET__EDITMODE] = $p_editmode;
        isys_module_request::get_instance()
            ->_internal_set_private('m_get', $l_gets);

        return $this;
    } // function

    /**
     * Gets the template file.
     *
     * @return  string
     */
    public function get_template()
    {
        return $this->m_template_file;
    } // function

    /**
     * Gets the template-component.
     *
     * @return  isys_component_template
     */
    public function get_template_component()
    {
        return $this->m_template;
    } // function

    /**
     * Method which hides the buttons via smarty-rule.
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function hide_buttons()
    {
        $this->hide("tom.content.bottom.buttons");

        return $this;
    } // function

    /**
     * Hides all elements of a given smarty-area.
     *
     * @param   string $p_area
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function hide($p_area)
    {
        if (!empty($p_area))
        {
            $this->m_template->smarty_tom_add_rule($p_area . '.*.p_bInvisible=1');
        } // if

        return $this;
    } // function

    /**
     * Set UI Rules from properties.
     *
     * @param   array $p_properties
     *
     * @return  isys_cmdb_ui_category
     */
    public function process_ui_rules(array $p_properties)
    {
        $l_rules           = [];
        $l_custom_category = false;
        $l_request         = isys_request::factory();

        if (method_exists($this->m_catdao, 'get_object_id'))
        {
            if ($l_obj_id = $this->m_catdao->get_object_id())
            {
                $l_request->set_object_id($l_obj_id);
            } // if
        } // if

        if (method_exists($this->m_catdao, 'get_list_id'))
        {
            if (($l_list_id = $this->m_catdao->get_list_id()))
            {
                $l_request->set_category_data_id($l_list_id);
            } // if
        } // if

        if (get_class($this->m_catdao) === 'isys_cmdb_dao_category_g_custom_fields')
        {
            $l_custom_category = true;
        } // if

        foreach ($p_properties as $l_key => $l_property)
        {
            // Special handling with custom categories
            if ($l_custom_category && is_array($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]))
            {
                $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CATG__CUSTOM__' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
            } // if

            // Set UI Parameters.
            if (is_array($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]))
            {
                foreach ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] as $l_paramKey => $l_paramValue)
                {
                    if (is_scalar($l_paramValue) || is_array($l_paramValue))
                    {
                        if (!isset($l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_paramKey]) || !is_scalar(
                                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_paramKey]
                            )
                        )
                        {
                            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_paramKey] = $l_paramValue;
                        }
                        else
                        {
                            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_paramKey] .= $l_paramValue;
                        }
                    }
                    else if (is_object($l_paramValue) && $l_paramValue instanceof isys_callback)
                    {
                        $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_paramKey] = $l_paramValue->execute($l_request);
                    }
                    else
                    {
                        //throw new Exception('Attention: ' . $l_paramKey . ' not assigned to ui rules. Check it\s type in ' . __CLASS__ . ':' . __LINE__-10 . ' and if it may has to be assigned.');
                    }
                } // foreach
            } // if

            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strClass'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strClass'];
        } // foreach

        if (count($l_rules))
        {
            $this->get_template_component()
                ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
        } // if

        if (is_object($this->m_catdao) && is_a($this->m_catdao, 'isys_cmdb_dao_category'))
        {
            $this->process_ui_validation_rules($this->m_catdao, $l_rules);
        } // if

        return $this;
    } // function

    /**
     * Separated logic to process the validation rules, so that we can call this from the catg_overview UI class.
     *
     * @param   isys_cmdb_dao_category $p_dao
     * @param   array                  $p_rules The rules array are simply here to prevent "overwriting" something.
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process_ui_validation_rules(isys_cmdb_dao_category $p_dao, array $p_rules = [])
    {
        $l_validation      = $p_dao->get_validation();
        $l_properties      = $p_dao->get_properties($l_validation ? C__PROPERTY__WITH__VALIDATION : null);
        $l_rules           = $p_rules;
        $l_custom_category = false;

        if (get_class($p_dao) === 'isys_cmdb_dao_category_g_custom_fields')
        {
            $l_custom_category = true;
        } // if

        foreach ($l_properties as $l_property)
        {
            $l_validation = ($l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] || $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] || $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] || $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL]);

            // Special handling with custom categories
            if ($l_custom_category === true && isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]))
            {
                $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CATG__CUSTOM__' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
            } // if

            if ($l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY])
            {
                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strClass'] .= ' validate-mandatory';
            } // if

            if ($l_validation || count($l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION]))
            {
                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strClass'] .= ' validate-rule';
            } // if

            // Display the input field as select!
            $l_is_textinput = in_array(
                $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE],
                [
                    C__PROPERTY__UI__TYPE__TEXTAREA,
                    C__PROPERTY__UI__TYPE__TEXT
                ]
            );
            $l_as_select    = (isset($l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION]) && is_array(
                    $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION]
                ) && $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] == 'VALIDATE_BY_TEXTFIELD' && $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['as-select'] === true);

            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['force_dialog']      = ($l_is_textinput && $l_as_select);
            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['force_dialog_data'] = explode(
                "\n",
                $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['value']
            );
        } // foreach

        if (count($l_rules))
        {
            $this->get_template_component()
                ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
        } // if

        return $this;
    } // function

    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  array
     * @global  array                  $index_includes
     * @throws  isys_exception_dao_cmdb
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_validation = $p_cat->get_validation();
        $l_properties = $p_cat->get_properties($l_validation ? C__PROPERTY__WITH__VALIDATION : null);

        $this->process_ui_rules($l_properties);

        $l_data = [];

        // Set object id from get parameters.
        if (isset($_GET[C__CMDB__GET__OBJECT]))
        {
            $this->m_object_id = $_GET[C__CMDB__GET__OBJECT];
        }
        elseif(method_exists($p_cat, 'get_object_id'))
        {
            $this->m_object_id = $p_cat->get_object_id();
        } // if

        // Set category data id from get or post parameters.
        if (!$this->m_category_data_id)
        {
            if (!($this->m_category_data_id = isset($_GET[C__CMDB__GET__CATLEVEL]) ? $_GET[C__CMDB__GET__CATLEVEL] : $_POST[C__GET__ID]))
            {
                $this->m_category_data_id = -1;
            } // if

            if (is_array($this->m_category_data_id))
            {
                if (is_numeric($this->m_category_data_id[0]))
                {
                    $this->m_category_data_id = $this->m_category_data_id[0];
                }
                else
                {
                    $this->m_category_data_id = -1;
                } // if
            } // if
        }
        elseif(method_exists($p_cat, 'get_list_id'))
        {
            $this->m_category_data_id = $p_cat->get_list_id();
        } // if

        if ($l_validation === false)
        {
            $l_data = $p_cat->parse_user_data();
        }
        else
        {
            if ($p_cat->is_multivalued())
            {
                if ($_POST[C__GET__NAVMODE] != C__NAVMODE__NEW)
                {
                    $l_data = $p_cat->get_data($this->m_category_data_id)
                        ->__to_array();
                } // if
            }
            else
            {
                $l_data = $p_cat->get_general_data();
                if (is_null($l_data) && isset($this->m_object_id))
                {
                    $l_data = $p_cat->get_data(null, $this->m_object_id)
                        ->__to_array();
                } // if
            } // if
        } // if

        $l_rules = [];

        foreach ($l_properties as $l_key => $l_property)
        {
            // Field:
            $l_value = null;
            if ($l_validation === true)
            {
                $l_value = $l_data[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
            }
            else
            {
                $l_value = $l_data[$l_key];
            } // if

            // Type:
            $l_type = null;
            switch ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
            {
                case C__TYPE__DATE:
                    $l_value = strtotime($l_value);
                    $l_value = date('Y-m-d', $l_value);
                    $l_type  = 'p_strValue';
                    break;
                case C__TYPE__DATE_TIME:
                    $l_value = strtotime($l_value);
                    $l_value = date('Y-m-d H:i:s', $l_value);
                    $l_type  = 'p_strValue';
                    break;
                case C__TYPE__TEXT:
                case C__TYPE__TEXT_AREA:
                case C__TYPE__FLOAT:
                case C__TYPE__DOUBLE:
                case C__TYPE__INT:
                    $l_type = 'p_strValue';

                    if (isset($l_property[C__PROPERTY__DATA__REFERENCES]) || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG_LIST)
                    {
                        $l_type = 'p_strSelectedID';

                        if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])
                        {
                            if (is_object($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && is_a(
                                    $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'],
                                    'isys_callback'
                                )
                            )
                            {
                                $l_request                                                              = isys_request::factory()
                                    ->set_object_id($p_cat->get_object_id())
                                    ->set_category_data_id($this->m_category_data_id);
                                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_arData'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute(
                                    $l_request
                                );
                            }
                            else
                            {
                                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_arData'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                            } // if
                        }
                        else if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'])
                        {
                            if (is_object($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']) && is_a(
                                    $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'],
                                    'isys_callback'
                                )
                            )
                            {
                                $l_request                                                                = isys_request::factory()
                                    ->set_object_id($p_cat->get_object_id())
                                    ->set_category_data_id($this->m_category_data_id);
                                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strValue'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']->execute(
                                    $l_request
                                );

                                /**
                                 * Set value to null, because a correct p_strValue assigned one line up should be enough. p_strSelectedID is overwritten in line ~513
                                 * with: $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_type] = $l_value;
                                 * This results in an unexpected behaviour when p_strValue is null or unset, and $l_value is.
                                 */
                                $l_value = null;
                            }
                            else
                            {
                                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strValue'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'];
                            } // if
                        }
                        else if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][$l_type])
                        {
                            if (is_object($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][$l_type]) && is_a(
                                    $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][$l_type],
                                    'isys_callback'
                                )
                            )
                            {
                                $l_request = isys_request::factory()
                                    ->set_object_id($p_cat->get_object_id())
                                    ->set_category_data_id($this->m_category_data_id);
                                $l_value   = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][$l_type]->execute($l_request);
                            }
                            else
                            {
                                $l_value = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][$l_type];
                            } // if
                        } // if
                    } // if

                    if (!isset($l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_type]) && !$l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_type] && !$l_value && $l_value !== '0')
                    {
                        if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT])
                        {
                            $l_value = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT];
                        }
                    }

                    break;

                default:
                    throw new isys_exception_dao_cmdb(
                        sprintf(
                            'Category %s: Cannot prepare entity because of unknown type "%s".',
                            $p_cat->get_category_const(),
                            $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]
                        ), $p_cat
                    );
            } // switch

            // Callback for certain n:m logics.
            switch ($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])
            {
                case 'contact':
                    $l_person_ids = [];
                    $l_person_dao = new isys_cmdb_dao_category_g_contact($p_cat->get_database_component());
                    $l_person_res = $l_person_dao->get_assigned_contacts_by_relation_id($l_value);

                    // Save some resources.
                    unset($l_person_dao);

                    while ($l_row = $l_person_res->get_row())
                    {
                        $l_person_ids[] = $l_row['isys_obj__id'];
                    } // while

                    if (count($l_person_ids) > 0)
                    {
                        $l_value = isys_format_json::encode($l_person_ids);
                    } // if
                    break;
                case 'connection':
                    // Check for setted connected object ID first
                    if (isset($l_data['isys_connection__isys_obj__id']))
                    {
                        // Set value
                        $l_value = $l_data['isys_connection__isys_obj__id'];
                    }
                    break;
            } // switch

            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_type] = $l_value;

            if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPlaceholder']))
            {
                $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strPlaceholder'] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPlaceholder'];
            } // if
        } // foreach

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        // Activate the commentary field.
        $this->activate_commentary($p_cat);

        $index_includes['contentbottomcontent'] = $this->get_template();

        return $l_rules;
    } // function

    /**
     * Processes category data list for multi-valued categories.
     *
     * @param   isys_cmdb_dao_category $p_cat Category's DAO
     * @param   array                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_list = $p_cat->get_category_list();

        // Set object identifier:
        $l_object_id = null;
        if (isset($_GET[C__CMDB__GET__OBJECT]))
        {
            assert(is_numeric($_GET[C__CMDB__GET__OBJECT]) && $_GET[C__CMDB__GET__OBJECT] >= 0);
            $l_object_id = intval($_GET[C__CMDB__GET__OBJECT]);
        }
        else
        {
            //throw new isys_exception_cmdb('Error: Object identifier not send by HTTP GET.');
        } // if

        if (class_exists($l_list))
        {
            $l_field = null;
            if (!is_null($p_db_field_name))
            {
                $l_field = $p_db_field_name;
            }
            else
            {
                $l_field = $p_cat->get_source_table();
            } // if

            if ($this->m_csv_export)
            {
                isys_component_template_navbar::getInstance()
                    ->set_visible(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
                    ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);
            }
            else
            {
                isys_component_template_navbar::getInstance()
                    ->set_visible(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
                    ->set_active(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV);
            } // if

            $this->list_view($l_field, $l_object_id, new $l_list($p_cat), $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink);

            $this->hide_buttons();
        }
        else
        {
            if (empty($l_list))
            {
                throw new isys_exception_general('List class empty for "' . get_class($this) . '".');
            }
            else
            {
                throw new isys_exception_general('List class "' . $l_list . '" does not exist.');
            } // if
        } // if

        return null;
    } // function

    /**
     * Initializes the category's view.
     *
     * @param   isys_cmdb_dao_category &$p_cat Category's DAO
     *
     * @return  void
     */
    public function init(isys_cmdb_dao_category &$p_cat)
    {
        $this->m_catdao   = $p_cat;
        $l_strObjTypeName = null;

        if ($_GET[C__CMDB__GET__OBJECTTYPE])
        {
            $l_strObjTypeName = $p_cat->get_objtype_name_by_id_as_string($_GET[C__CMDB__GET__OBJECTTYPE]);
        }
        else
        {
            $l_arData         = $p_cat->get_general_data();
            $l_nObjTypeID     = $l_arData["isys_obj__isys_obj_type__id"];
            $l_strObjTypeName = $p_cat->get_objtype_name_by_id_as_string($l_nObjTypeID);
        } // if

        $this->get_template_component()
            ->assign("content_title", _L($l_strObjTypeName))
            ->assign("categoryTitle", $this->gui_get_title($p_cat))
            ->assign("commentarySuffix", $p_cat->get_category_type() . $p_cat->get_category_id());
    } // function

    /**
     * Overwrite this method in order to override the parameter whether the selected category is multivalued.
     * Return false for a single-valued handling, true for a multi-valued behaviour and null, if the category view/list
     * (isys_cmdb_view_category / isys_cmdb_list_category) has to use the database entry (from the isysgui_cat* tables).
     *
     * @author      Andre Woesten <awoesten@i-doit.org>
     * @return      mixed  Boolean or null
     * @deprecated  Use Category's DAO's method is_multivued() instead.
     */
    public function is_multivalued()
    {
        return null;
    } // function

    /**
     * Base method for the menutree extension (Dummy).
     *
     * @deprecated
     * @return  array
     */
    public function get_menutree_extension()
    {
        return null;
    } // function

    /**
     * Gets additional template after normal template if exists.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_additional_template_after()
    {
        global $index_includes;

        if (isset($index_includes['contentbottomcontentaddition']))
        {
            return $index_includes['contentbottomcontentaddition'];
        } // if
    } // function

    /**
     * Gets additional template before normal template if exists.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_additional_template_before()
    {
        global $index_includes;

        if (isset($index_includes['contentbottomcontentadditionbefore']))
        {
            return $index_includes['contentbottomcontentadditionbefore'];
        } // if
    }

    /**
     * Shows the object browser instead of creating a new element when clicking on 'new'.
     * $p_params are isys_component_object_browser_ng parameters.
     *
     * @param  array  $p_params
     * @param  string $p_title
     * @param  string $p_tooltip
     * @param  string $p_hiddenfield
     */
    protected function object_browser_as_new($p_params, $p_title, $p_tooltip, $p_hiddenfield = null)
    {
        $l_instance     = new isys_popup_browser_object_ng();
        $l_strJs        = $l_instance->get_js_handler($p_params);
        $l_tpl_navbar   = isys_component_template_navbar::getInstance();
        $l_exclude_btns = [C__NAVBAR_BUTTON__NEW];

        if ($l_tpl_navbar->is_active(C__NAVBAR_BUTTON__QUICK_PURGE))
        {
            $l_exclude_btns[] = C__NAVBAR_BUTTON__QUICK_PURGE;
        } // if

        $l_tpl_navbar->hide_all_buttons($l_exclude_btns)
            ->deactivate_all_buttons($l_exclude_btns)
            ->set_js_onclick($l_strJs, C__NAVBAR_BUTTON__NEW)
            ->set_active(true, C__NAVBAR_BUTTON__PURGE)
            ->set_title(_L($p_title), C__NAVBAR_BUTTON__NEW)
            ->set_tooltip(_L($p_tooltip), C__NAVBAR_BUTTON__NEW);

        if ($p_hiddenfield !== null)
        {
            $l_tpl_navbar->set_hidden_field($p_hiddenfield);
        } // if
    } // function

    /**
     * Shows the object-relation browser instead of creating a new element when clicking on 'new'.
     * $p_params are isys_component_object_browser_relation parameters.
     *
     * @param  array  $p_params
     * @param  string $p_title
     * @param  string $p_tooltip
     */
    protected function object_relation_browser_as_new($p_params, $p_title, $p_tooltip)
    {
        $p_params[isys_popup_browser_object_relation::C__SECOND_LIST] = [
            ['isys_cmdb_dao_category_g_relation::object_browser_get_data_by_object_and_relation_type'],
            $p_params[isys_popup_browser_object_relation::C__RELATION_FILTER]
        ];

        $l_instance = new isys_popup_browser_object_relation();
        $l_strJs    = $l_instance->get_js_handler($p_params);

        isys_component_template_navbar::getInstance()
            ->set_js_onclick($l_strJs, C__NAVBAR_BUTTON__NEW)
            ->set_title(_L($p_title), C__NAVBAR_BUTTON__NEW)
            ->set_tooltip(_L($p_tooltip), C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__SAVE)
            ->set_active(false, C__NAVBAR_BUTTON__CANCEL)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW);
    } // function

    /**
     * Build the category list.
     *
     * @param   string                  $p_strCatDBName
     * @param   integer                 $p_objID
     * @param   isys_cmdb_dao_list_catg $p_daoObj
     * @param   array                   $p_getOverride
     * @param   string                  $p_strVarName
     * @param   string                  $p_strTemplateName
     * @param   boolean                 $p_bCheckbox
     * @param   boolean                 $p_bOrderLink
     *
     * @throws  isys_exception_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    protected function list_view($p_strCatDBName, $p_objID, $p_daoObj = null, $p_getOverride = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true)
    {
        global $index_includes;

        try
        {
            $l_listdao = null;

            if (!$p_strVarName)
            {
                $p_strVarName = "objectTableList";
            } // if

            if (!$p_strTemplateName)
            {
                $p_strTemplateName = "object_table_list.tpl";
            } // if

            $l_tpl = $this->m_template;
            $l_db  = $this->m_database_component;

            // Prepare needed components.
            $l_gets = isys_module_request::get_instance()
                ->get_gets();

            // Determine responsible DAO object.
            if ($p_daoObj != null && $p_daoObj != "")
            {
                $l_listdao = $p_daoObj;
            }
            else
            {
                $l_listdao = new isys_cmdb_dao_list_catg($l_db);
            } // if

            // Is the table name unset?
            if (empty($p_strCatDBName))
            {
                $l_field = "";

                try
                {
                    $l_res = $l_listdao->get_result($p_strCatDBName, $p_objID);
                    $l_row = $l_res->get_row();

                    if (is_array($l_row))
                    {
                        $l_field = key($l_row);
                    }
                }
                catch (isys_exception $e)
                {
                    isys_glob_display_error($e->getMessage());
                } // try
            }
            else
            {
                if (strstr($p_strCatDBName, "__id") || $p_strCatDBName == "isys_id")
                {
                    $l_field = $p_strCatDBName;
                }
                else if (((strstr($p_strCatDBName, "_list") || strstr($p_strCatDBName, "_item")) && (!strstr($p_strCatDBName, "_listener") || strstr(
                            $p_strCatDBName,
                            "_listener_list"
                        )))
                )
                {
                    $l_field = $p_strCatDBName . "__id";
                }
                else
                {
                    $l_field = $p_strCatDBName . "_list__id";
                } // if
            } // if

            $l_listdao->set_rec_status($_SESSION["cRecStatusListView"]);

            $l_arData = $l_listdao->get_rec_array();

            // @see  ID-2841 Deactivate buttons, if the list is empty.
            if ($l_listdao->get_rec_counts()[$l_listdao->get_rec_status()] === 0)
            {
                $l_navbar = isys_component_template_navbar::getInstance();

                array_map(
                    function ($p_button) use ($l_navbar)
                    {
                        if ($l_navbar->is_active($p_button))
                        {
                            $l_navbar->set_active(false, $p_button);
                        } // if
                    },
                    [C__NAVBAR_BUTTON__ARCHIVE, C__NAVBAR_BUTTON__DELETE, C__NAVBAR_BUTTON__PURGE, C__NAVBAR_BUTTON__QUICK_PURGE, C__NAVBAR_BUTTON__RECYCLE]);
            } // if

            $l_tpl->smarty_tom_add_rule("tom.content.top.filter.p_strValue=" . isys_glob_get_param("filter"))
                ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=0")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=" . ($l_listdao->rec_status_list_active() ? "0" : "1"))
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $_SESSION["cRecStatusListView"])
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($l_arData))
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=" . ($l_listdao->rec_status_list_active() ? "0" : "1"))// Set the save buttons to invisible.
                ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
                ->assign("bNavbarFilter", "1")
                ->assign("bShowCommentary", "0");

            $l_nCatType = $l_listdao->get_category_type();
            $l_nCat     = $l_listdao->get_category();

            // Request data from DAO.
            try
            {
                $l_signal_list = isys_component_signalcollection::get_instance()
                    ->emit(
                        "mod.cmdb.resultList",
                        $l_nCat,
                        $l_nCatType,
                        $p_objID,
                        $_SESSION["cRecStatusListView"]
                    );
                $l_use_signal  = false;
                if (is_array($l_signal_list))
                {
                    $l_res = array_shift($l_signal_list);
                    if (is_object($l_res))
                    {
                        $l_use_signal = true;
                    }
                }

                if (!$l_use_signal) $l_res = $l_listdao->get_result($p_strCatDBName, $p_objID);
            }
            catch (isys_exception $e)
            {
                isys_glob_display_error($e->getMessage());
            } // try

            if (isset($l_res) && $l_res)
            {
                // Create list.
                $l_list = isys_component_list::factory(
                    null,
                    $l_res,
                    $l_listdao,
                    $l_listdao->get_rec_status(),
                    (isys_module_request::get_instance()
                        ->get_post(C__GET__NAVMODE) == C__NAVMODE__EXPORT_CSV ? 'csv' : 'html')
                );

                // Create URL.
                $l_jumpgets = $l_gets;
                unset($l_jumpgets[C__CMDB__GET__CAT_LIST_VIEW], $l_jumpgets[C__CMDB__GET__EDITMODE], $l_jumpgets["mNavID"]);

                // Set viewmode to category detail view.
                $l_jumpgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

                $l_jumpgets[C__CMDB__GET__CATLEVEL] = "[{" . $l_field . "}]";

                // Set new category parameters.
                if ($l_nCat)
                {
                    unset($l_jumpgets[C__CMDB__GET__CATG], $l_jumpgets[C__CMDB__GET__CATS], $l_jumpgets[C__GET__AJAX_CALL], $l_jumpgets["ajax"]);

                    switch ($l_nCatType)
                    {
                        case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                            $l_jumpgets[C__CMDB__GET__CATS] = $l_nCat;
                            break;

                        case C__CMDB__CATEGORY__TYPE_GLOBAL:
                        default:
                            $l_jumpgets[C__CMDB__GET__CATG] = $l_nCat;
                            break;
                    } // switch
                } // if

                // Any GET-Parameters to override?
                if (is_array($p_getOverride))
                {
                    $l_jumpgets = array_merge($l_jumpgets, $p_getOverride);
                } // if

                // Determine URL.
                if (method_exists($l_listdao, "make_row_link"))
                {
                    $l_jumpurl = urldecode($l_listdao->make_row_link($l_jumpgets));
                }
                else
                {
                    $l_jumpurl = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_jumpgets)));
                } // if

                $l_strCheckboxValue = "";

                if ($p_bCheckbox)
                {
                    $l_strCheckboxValue = "[{" . $l_field . "}]";
                } // if

                // Configure list.
                $l_list_fields = $l_listdao->get_fields($p_strCatDBName);

                $l_extended_fields = isys_component_signalcollection::get_instance()
                    ->emit(
                        "mod.cmdb.extendFieldList",
                        $l_nCat,
                        $l_nCatType
                    );

                if (is_array($l_extended_fields))
                {
                    $l_extended_list_fields = array_shift($l_extended_fields);

                    if (!empty($l_extended_list_fields) && is_array($l_extended_list_fields))
                    {
                        $l_list_fields = array_merge($l_list_fields, $l_extended_list_fields);
                    }
                }

                $l_list->config($l_list_fields, $l_jumpurl, $l_strCheckboxValue, true, $p_bOrderLink);

                // Create list.
                if ($l_list->createTempTable())
                {
                    // Assign evaluated list into template.
                    $l_tpl->assign($p_strVarName, $l_list->getTempTableHtml());

                    $index_includes['contentbottomcontent'] = "content/bottom/content/" . $p_strTemplateName;
                }
                else
                {
                    throw new isys_exception_cmdb(
                        "Could not create temp table for isys_cmdb_ui_category::list_view(" . $p_strCatDBName . " - ID: " . $p_objID . ")", C__CMDB__ERROR__CATEGORY_PROCESSOR
                    );
                } // if
            }
            else
            {
                throw new isys_exception_cmdb("get_result() is invalid for current list class (" . get_class($l_listdao) . ")", C__CMDB__ERROR__CATEGORY_PROCESSOR);
            } // if
        }
        catch (Exception $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try
    } // function

    /**
     * Constructs an user interface object for CMDB use.
     *
     * @global                          $index_includes
     * @global                          $g_comp_database
     *
     * @param   isys_component_template & $p_template
     *
     * @throws  isys_exception_ui
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->m_database_component = isys_application::instance()->database;
        $this->m_template           = $p_template;

        $p_template->include_template('contenttop', 'content/top/main_objectdetail.tpl');

    } // function
} // class