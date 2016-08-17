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
 * AJAX
 *
 * @package     Modules
 * @subpackage  Import
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6.1
 */
class isys_ajax_handler_csv_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'delete_profile':
                    $l_return['data'] = $this->delete_profile($_POST['profileID']);
                    break;

                case 'load_special_assignment':
                    $l_return['data'] = $this->load_special_assignment($_POST['categoryConst'], $_POST['propertyKey']);
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Method for deleting a given profile ID.
     *
     * @param   integer $p_profile_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function delete_profile($p_profile_id)
    {
        if ($p_profile_id > 0)
        {
            if (isys_module_import_csv::delete_profile($p_profile_id))
            {
                isys_notify::success(_L('LC__MODULE__IMPORT__CSV__MSG__DELETE'));
            }
            else
            {
                isys_notify::error(_L('LC__MODULE__IMPORT__CSV__MSG__DELETE_FAIL'));
            } // if
        }
        else
        {
            isys_notify::info(_L('LC__MODULE__IMPORT__CSV__MSG__DELETE_NO_SELECTION'));
        } // if

        return true;
    } // function

    /**
     * This method will return data if the given property can get other objects assigned. Optionally there's a object-type whitelist.
     *
     * @param   string $p_category
     * @param   string $p_property
     *
     * @return  mixed
     * @throws  isys_exception_general
     */
    protected function load_special_assignment($p_category, $p_property)
    {
        $l_category_map = isys_module_import_csv::get_category_map();

        if (!isset($l_category_map[$p_category]))
        {
            return false;
        } // if

        if (!is_array($l_category_map[$p_category][isys_module_import_csv::CL__CAT__PROPERTIES]))
        {
            return false;
        } // if

        if (!isset($l_category_map[$p_category][isys_module_import_csv::CL__CAT__PROPERTIES][$p_property]))
        {
            return false;
        } // if

        $l_prop_data = $l_category_map[$p_category][isys_module_import_csv::CL__CAT__PROPERTIES][$p_property];

        // Check, if the given attribut is a "assignment" field for other objects.
        if (in_array(
                $l_prop_data[isys_module_import_csv::CL__CAT__PROPERTY__MODE],
                [
                    'contact',
                    'connection',
                    'object',
                    'custom_category_property_object',
                    'layer_2_assignments'
                ]
            ) || ($p_category . $p_property === 'C__CATG__LOCATIONparent')
        )
        {
            $l_obj_type_whitelist = [];

            // Here we try to get a object-type whitelist. This currently only works with properties which have object browsers in the UI with set filters.
            if (class_exists($l_category_map[$p_category][isys_module_import_csv::CL__CAT__CLASS]) && method_exists(
                    $l_category_map[$p_category][isys_module_import_csv::CL__CAT__CLASS],
                    'get_properties'
                )
            )
            {
                $l_dao = call_user_func(
                    [
                        $l_category_map[$p_category][isys_module_import_csv::CL__CAT__CLASS],
                        'instance'
                    ],
                    $this->m_database_component
                );

                $l_properties = $l_dao->get_properties();

                if (isset($l_properties[$p_property]))
                {
                    $l_is_object_browser = ($l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP) && ($l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_ng');

                    $l_has_filter = isset($l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__CAT_FILTER]) || isset($l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__TYPE_FILTER]) || isset($l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__GROUP_FILTER]);

                    if ($l_is_object_browser && $l_has_filter)
                    {
                        $l_obj_type_whitelist = (new isys_popup_browser_object_ng())->get_objecttype_filter(
                            array_filter(
                                explode(';', $l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__GROUP_FILTER]) ?: []
                            ),
                            array_filter(
                                explode(';', $l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__TYPE_FILTER]) ?: []
                            ),
                            array_filter(
                                explode(';', $l_properties[$p_property][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__CAT_FILTER]) ?: []
                            ),
                            [],
                            true
                        );
                    } // if
                } // if
            } // if

            return [
                'connection'         => true,
                'obj_type_whitelist' => $l_obj_type_whitelist
            ];
        } // if

        return false;
    } // function
} // class