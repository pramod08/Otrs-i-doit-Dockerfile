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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CSV import handler
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9.8
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_import_handler_csv extends isys_import_handler
{
    const c__exist_check__object           = 1;
    const c__exist_check__sysid            = 2;
    const c__exist_check__sysid_object     = 3;
    const c__exist_check__object_by_type   = 4;
    const c__exist_check__import_specified = 5;
    /**
     * Determines if new objects should be created or not.
     *
     * @var  boolean
     */
    protected $m_create_new_objects = true;
    /**
     * CSV header.
     *
     * @var  array
     */
    protected $m_csv_head = [];
    /**
     * Default object type, if type_column is not defined.
     *
     * @var  integer
     */
    protected $m_default_type = C__OBJTYPE__SERVER;
    /**
     * Import first csv line or discard it?
     *
     * @var  boolean
     */
    protected $m_discard_fist_line = true;
    /**
     * How many lines should be discarded.
     *
     * @var  integer
     */
    protected $m_discarded_lines = 1;
    /**
     * Check for existing objects by.
     *
     * @var  integer
     */
    protected $m_exist_check = 1;
    /**
     * Determine which column is the identifier to check (for example email adress in table person).
     *
     * @var  boolean
     */
    protected $m_identifier = false;
    /**
     * Object id column.
     *
     * @var  boolean
     */
    protected $m_obj_id_column = false;
    /**
     * Shall the objects be created new?
     *
     * @var  boolean
     */
    protected $m_object_new = false;
    /**
     * Adds additional text in front of object title.
     *
     * @var  string
     */
    protected $m_object_title_additional = '';
    /**
     * Default object type mapping.
     *
     * @var  array
     */
    protected $m_objtype_map = ["server" => C__OBJTYPE__SERVER];
    /**
     * Overwrite if object already exists?
     *
     * @var  boolean
     */
    protected $m_overwrite = false;
    /**
     * CSV Separator.
     *
     * @var  string
     */
    protected $m_separator = ";";
    /**
     * Object sysid column.
     *
     * @var  integer
     */
    protected $m_sysid_column = 0;
    /**
     * Object title column.
     *
     * @var  integer
     */
    protected $m_title_column = 1;
    /**
     * Object type column (Content must be the constant of an object type).
     *
     * @var  integer
     */
    protected $m_type_column = 2;
    /**
     * Shall the objects be updated?
     *
     * @var  boolean
     */
    protected $m_update_object = true;
    /**
     * Cache array for the import.
     *
     * @var  array
     */
    private $m_cache_arr = [];
    /**
     * Content - Unused.
     *
     * @var  string
     */
    private $m_content = "";
    /**
     * Current line in the csv file.
     *
     * @var  integer
     */
    private $m_current_line = 0;
    /**
     * Array caching for dialog fields for the current csv import.
     *
     * @var  array
     */
    private $m_dialog_cache = [];
    /**
     * The current mode.
     *
     * @var  mixed
     */
    private $m_mode;
    /**
     * Start line in the csv file.
     *
     * @var  integer
     */
    private $m_start_line;

    /**
     * Format row method, must be implemented by child-classes.
     *
     * @abstract
     *
     * @param   string  $p_content
     * @param   array   $p_data
     * @param   integer $p_object_id
     *
     * @return  mixed
     */
    abstract protected function format_row($p_content, &$p_data, $p_object_id = null);

    /**
     * Check if the current object is new or not.
     *
     * @return  boolean
     */
    public function is_object_new()
    {
        return $this->m_object_new;
    } // function

    /**
     * Getter for getting how many lines inside the csv file will be ignored.
     *
     * @return  integer
     */
    public function get_discarded_lines()
    {
        return $this->m_discarded_lines;
    } // function

    /**
     * Getter for getting the current line which the import is currently is.
     *
     * @return  integer
     */
    public function get_current_line()
    {
        return $this->m_current_line;
    } // function

    /**
     * Resets the current line to starting point.
     *
     * @return  isys_import_handler_csv
     */
    public function reset_current_line()
    {
        $this->m_current_line = $this->m_discarded_lines;

        return $this;
    } // function

    /**
     * Returns the start-line.
     *
     * @return  integer
     */
    public function get_start_line()
    {
        return $this->m_start_line;
    } // function

    /**
     * Sets the start-line for the import.
     *
     * @param   integer $p_value
     *
     * @return  isys_import_handler_csv
     */
    public function set_start_line($p_value)
    {
        $this->m_start_line = (int) $p_value;

        return $this;
    } // function

    /**
     * Resets the starting-line.
     *
     * @return  isys_import_handler_csv
     */
    public function reset_start_line()
    {
        $this->m_start_line = $this->m_discarded_lines;

        return $this;
    } // function

    /**
     * Sets seperator which will be used to identify the values.
     *
     * @param   string $p_sep
     *
     * @return  isys_import_handler_csv
     */
    public function set_separator($p_sep = ";")
    {
        $this->m_separator = $p_sep;

        return $this;
    } // function

    /**
     * Method for loading the import-file content to the internat data-storage.
     *
     * @param   string $p_filename
     *
     * @return  boolean
     */
    public function load_import($p_filename)
    {
        $this->m_data = [];

        $l_file = fopen($p_filename, "r");

        while ($l_line = fgetcsv($l_file, null, $this->m_separator))
        {
            $this->m_data[] = $l_line;
        } // while

        fclose($l_file);

        return (count($this->m_data) > 0);
    } // function

    /**
     * Method for parsing the CSV data.
     *
     * @param   string $p_csv_data
     *
     * @return  boolean
     */
    public function parse($p_csv_data = null)
    {
        return true;
    } // function

    /**
     * Sets mode
     *
     * @param unknown_type $p_value
     */
    public function set_mode($p_value)
    {
        $this->m_mode = $p_value;
    }

    /**
     * Gets mode
     *
     * @return unknown
     */
    public function get_mode()
    {
        return $this->m_mode;
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_capacity
     * @param unknown_type $p_manufacturer
     * @param unknown_type $p_type
     *
     * @return unknown
     */
    public function parse_memory($p_title, $p_capacity, $p_manufacturer = null, $p_type = null)
    {

        $l_manufacturer = $p_manufacturer;
        $l_type         = $p_type;

        if (is_null($p_title)) $l_title = $p_capacity;
        else $l_title = $p_title;

        foreach ([
                     "TB",
                     "GB",
                     "MB",
                     "KB"
                 ] as $l_u)
        {
            if (strstr(strtolower($l_title), strtolower($l_u)))
            {
                $l_unit = $l_u;
                break;
            }
        }

        return new isys_import_helper(
            [], '', [
                "title"        => $l_title,
                "manufacturer" => $l_manufacturer,
                "type"         => $l_type,
                "unit"         => $l_unit,
                "quantity"     => 1,
                "capacity"     => $p_capacity
            ]
        );
    } // function

    /**
     * Deprecated
     *
     * @param unknown_type $p_content
     * @param unknown_type $p_manufacturer
     * @param unknown_type $p_frequency
     * @param unknown_type $p_type
     *
     * @return unknown
     */
    public function parse_cpu($p_content, $p_manufacturer = null, $p_frequency = null, $p_type = null)
    {
        return new isys_import_helper(
            [], $p_content, [
                "title"        => $p_content,
                "manufacturer" => $p_manufacturer,
                "frequency"    => $p_frequency,
                "type"         => $p_type
            ]
        );
    }

    /**
     * Parses the global category "global" in an allowed array for the import
     *
     * @param string $p_category
     * @param string $p_purpose
     * @param string $p_sysid
     * @param string $p_description
     * @param int    $p_status
     * @param string $p_title
     *
     * @return array
     */
    public function parse_global($p_category = null, $p_purpose = null, $p_sysid = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_cmdb_status = C__CMDB_STATUS__IN_OPERATION)
    {

        if (!empty($p_category)) $p_category = isys_import_handler::check_dialog('isys_catg_global_category', $p_category);
        else $p_category = null;

        if (!empty($p_purpose)) $p_purpose = isys_import_handler::check_dialog('isys_purpose', $p_purpose);
        else $p_purpose = null;

        if (is_string($p_cmdb_status) && !is_numeric($p_cmdb_status))
        {
            if (count($this->m_dialog_cache) > 0)
            {
                $l_cmdb_status = $this->get_dialog_cache('isys_cmdb_status', $p_cmdb_status);
            }
            else
            {
                $l_cmdb_status = isys_import_handler::check_dialog('isys_cmdb_status', $p_cmdb_status);
            }
        }
        else
        {
            $l_cmdb_status = $p_cmdb_status;
        }

        return [
            [
                'properties' => [
                    'category'    => ['value' => $p_category],
                    'purpose'     => ['value' => $p_purpose],
                    'sysid'       => ['value' => $p_sysid],
                    'description' => ['value' => $p_description],
                    'title'       => ['value' => $p_title],
                    'cmdb_status' => ['value' => $l_cmdb_status]
                ]
            ]
        ];
    }

    /**
     * Depreacted
     *
     * @param unknown_type $p_category
     * @param unknown_type $p_purpose
     * @param unknown_type $p_sysid
     * @param unknown_type $p_description
     * @param unknown_type $p_status
     * @param unknown_type $p_title
     *
     * @return unknown
     */
    public function _parse_global($p_category, $p_purpose = null, $p_sysid = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null)
    {

        if (!empty($p_category)) $p_category = isys_import_handler::check_dialog('isys_catg_global_category', $p_category);
        else $p_category = null;

        if (!empty($p_purpose)) $p_purpose = isys_import_handler::check_dialog('isys_purpose', $p_purpose);
        else $p_purpose = null;

        return [
            "category"    => $p_category,
            "purpose"     => $p_purpose,
            "description" => $p_description,
            "object"      => [
                "sysid"  => $p_sysid,
                // NULL = Don't change
                "status" => $p_status
            ]
        ];
    }

    /**
     * Parses the global category "accounting" in an allowed array for the import
     *
     * @param string $p_account_no
     * @param string $p_acquirementdate
     * @param int    $p_guarantee_period
     * @param string $p_price
     * @param string $p_referenced_contact
     * @param string $p_inventory_number
     * @param string $p_order_no
     * @param string $p_invoice_no
     * @param string $p_description
     *
     * @return array
     */
    public function parse_accounting($p_account_no, $p_acquirementdate = null, $p_guarantee_period = null, $p_price = null, $p_referenced_contact = null, $p_inventory_number = null, $p_order_no = null, $p_invoice_no = null, $p_description = null, $p_guarantee_period_unit = null)
    {
        global $g_comp_database;

        if ($p_referenced_contact)
        {
            $l_contact_dao = new isys_cmdb_dao_category_s_organization($g_comp_database);
            $l_contact_ref = new isys_contact_dao_reference($g_comp_database);

            $l_contact_res = $l_contact_dao->get_data(null, null, "AND isys_obj__title = '" . $p_referenced_contact . "'");
            if ($l_contact_res->num_rows() > 0)
            {
                $l_row     = $l_contact_res->get_row();
                $l_con_res = $l_contact_ref->get_data_item($l_row['isys_obj__id']);

                if ($l_con_res->num_rows() > 0)
                {
                    $l_contact    = $l_con_res->get_row();
                    $l_contact_id = $l_contact['isys_contact_2_isys_obj__isys_contact__id'];
                }
                else
                {
                    $l_contact_ref->insert_data_item($l_row['isys_obj__id']);
                    $l_contact_ref->save();
                    $l_contact_id = $l_contact_ref->get_id();
                    $l_contact_ref->clear();
                }
            }
            else
            {
                $l_obj_id = $l_contact_dao->insert_new_obj(C__OBJTYPE__ORGANIZATION, false, $p_referenced_contact, null, C__RECORD_STATUS__NORMAL);
                $l_contact_dao->create($l_obj_id, C__RECORD_STATUS__NORMAL, $p_referenced_contact, null, null, null, null, null, null, null, null, null, null);
                $l_contact_ref->insert_data_item($l_obj_id);
                $l_contact_ref->save();
                $l_contact_id = $l_contact_ref->get_id();
                $l_contact_ref->clear();
            }
        }

        if (isset($this->m_dialog_cache))
        {
            if ($p_guarantee_period_unit) $l_guarantee_period_unit = $this->get_dialog_cache('isys_guarantee_period_unit', $p_guarantee_period_unit);
            else $l_guarantee_period_unit = null;

            if ($p_account_no) $l_account_no = $this->get_dialog_cache('isys_account', $p_account_no);
            else $l_account_no = null;
        }
        else
        {
            if ($p_guarantee_period_unit) $l_guarantee_period_unit = isys_import_handler::check_dialog('isys_guarantee_period_unit', $p_guarantee_period_unit);
            else $l_guarantee_period_unit = null;

            if ($p_account_no) $l_account_no = isys_import_handler::check_dialog('isys_account', $p_account_no);
            else $l_account_no = null;
        }

        if ($p_price != '')
        {
            unset($l_matches);
            preg_match("/^\d+(\,\d{1,2}|\.\d{1,2})?|-/i", $p_price, $l_matches);
            $p_price = $l_matches[0];
        }

        $l_return = [
            [
                'properties' => [
                    'account'               => ['value' => $l_account_no],
                    'inventory_no'          => ['value' => $p_inventory_number],
                    'acquirementdate'       => ['value' => date('d.m.Y H:i:s', $p_acquirementdate)],
                    'guarantee_period'      => ['value' => $p_guarantee_period],
                    'contact'               => ['value' => $l_contact_id],
                    'price'                 => ['value' => $p_price],
                    'order_no'              => ['value' => $p_order_no],
                    'invoice_no'            => ['value' => $p_invoice_no],
                    'description'           => ['value' => $p_description],
                    'guarantee_period_unit' => ['value' => $l_guarantee_period_unit]
                ]
            ]
        ];

        return $l_return;
    }

    /**
     * @deprecated
     *
     * @param unknown_type $p_account_no
     * @param unknown_type $p_acquirementdate
     * @param unknown_type $p_guarantee_period
     * @param unknown_type $p_price
     * @param unknown_type $p_referenced_contact
     * @param unknown_type $p_inventory_number
     * @param unknown_type $p_order_no
     * @param unknown_type $p_invoice_no
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function _parse_accounting($p_account_no, $p_acquirementdate = null, $p_guarantee_period = null, $p_price = null, $p_referenced_contact = null, $p_inventory_number = null, $p_order_no = null, $p_invoice_no = null, $p_description = null)
    {
        $l_return = [
            "account"          => new isys_import_helper(
                [
                    "title_lang" => $p_account_no
                ], $p_account_no
            ),
            "acquirementdate"  => date("Y-m-d H:i:s", $p_acquirementdate),
            "guarantee_period" => $p_guarantee_period,
            "price"            => $p_price,
            "inventory_no"     => $p_inventory_number,
            "order_no"         => $p_order_no,
            "invoice_no"       => $p_invoice_no,
            "description"      => $p_description,
        ];

        if ($p_referenced_contact)
        {

            $l_dao_contact  = new isys_cmdb_dao_category_s_organization_master($this->m_db);
            $l_contact_data = $l_dao_contact->get_data(null, null, "AND isys_obj__title = '" . $p_referenced_contact . "'");

            if ($l_contact_data->num_rows() > 0)
            {
                $l_row = $l_contact_data->get_row();

                $l_contacts = new isys_import_helper(
                    [
                        "id"    => $l_row["isys_obj__id"],
                        "type"  => C__OBJTYPE__ORGANIZATION,
                        "title" => $p_referenced_contact,
                    ], $l_row["isys_cats_organization_list__title"], [
                        "id"    => $l_row["isys_obj__id"],
                        "type"  => C__OBJTYPE__ORGANIZATION,
                        "title" => $p_referenced_contact,
                    ]
                );

            }
            else
            {

                $l_contacts = new isys_import_helper(
                    [
                        "id"    => "",
                        "type"  => C__OBJTYPE__ORGANIZATION,
                        "title" => $p_referenced_contact,
                    ], strtolower($p_referenced_contact), [
                        "id"    => "",
                        "type"  => C__OBJTYPE__ORGANIZATION,
                        "title" => $p_referenced_contact,
                    ]
                );
            }

            $l_return["contact"] = (object) [$l_contacts];
        }

        return $l_return;
    }

    /**
     * Parses the global category "model" in an allowed array for the import
     *
     * @param string $p_title
     * @param string $p_serial
     * @param string $p_manufacturer
     * @param string $p_productid
     * @param string $p_firmware
     * @param string $p_description
     *
     * @return array
     */
    public function parse_model($p_title, $p_serial, $p_manufacturer, $p_productid = null, $p_firmware = null, $p_description = null)
    {

        if (!empty($this->m_dialog_cache))
        {
            if (!empty($p_manufacturer)) $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_manufacturer);
            else $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', 'LC__UNIVERSAL__NOT_SPECIFIED');
        }
        else
        {
            if (!empty($p_manufacturer)) $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_manufacturer);
            else $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', 'LC__UNIVERSAL__NOT_SPECIFIED');
        }

        return [
            [
                'properties' => [
                    'title'        => ['value' => isys_import_handler::check_dialog('isys_model_title', $p_title, null, $l_manufacturer)],
                    'serial'       => ['value' => $p_serial],
                    'manufacturer' => ['value' => $l_manufacturer],
                    'productid'    => ['value' => $p_productid],
                    'firmware'     => ['value' => $p_firmware],
                    'description'  => ['value' => $p_description]
                ]
            ]
        ];

    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_serial
     * @param unknown_type $p_manufacturer
     * @param unknown_type $p_productid
     * @param unknown_type $p_firmware
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function _parse_model($p_title, $p_serial, $p_manufacturer, $p_productid = null, $p_firmware = null, $p_description = null)
    {
        return [
            [
                'properties' => [
                    'title'        => ['value' => isys_import_handler::check_dialog('isys_model_title', $p_title)],
                    'serial'       => ['value' => $p_serial],
                    'manufacturer' => ['value' => isys_import_handler::check_dialog('isys_model_manufacturer', $p_manufacturer)],
                    'productid'    => ['value' => $p_productid],
                    'firmware'     => ['value' => $p_firmware],
                    'description'  => ['value' => $p_description]
                ]
            ]
        ];

        $l_arr = [
            "title"        => $p_title,
            "serial"       => $p_serial,
            "manufacturer" => $p_manufacturer,
            "firmware"     => $p_firmware,
            "description"  => $p_description
        ];

        if ($p_productid) $l_arr["productid"] = $p_productid;

        return $l_arr;
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_interface_title
     * @param unknown_type $p_port_title
     *
     * @return unknown
     */
    public function parse_network($p_interface_title, $p_port_title = "Port")
    {
        return [
            "port" => [
                0 => new isys_import_helper(
                    [], $p_port_title, [
                        "title"     => $p_port_title,
                        "interface" => $p_interface_title
                    ]
                )
            ]
        ];
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_app
     * @param unknown_type $p_type
     *
     * @return unknown
     */
    public function parse_application($p_app, $p_type = "C__OBJTYPE__APPLICATION")
    {
        return new isys_import_helper(
            [
                "data_id" => -1
            ], "", [
                "application" => new isys_import_helper(
                    [
                        "id"   => -1,
                        "type" => $p_type
                    ], $p_app
                )
            ]
        );
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_os
     *
     * @return unknown
     */
    public function parse_operating_system($p_os)
    {
        return new isys_import_helper(
            [
                "data_id" => -1
            ], "", [
                "application" => new isys_import_helper(
                    [
                        "id"   => -1,
                        "type" => "C__OBJTYPE__OPERATING_SYSTEM"
                    ], $p_os
                )
            ]
        );
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_emergency_plan_title
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function parse_emergency_plan($p_title = null, $p_emergency_plan_title = null, $p_description = null)
    {

        if (!is_null($p_emergency_plan_title))
        {
            $l_emergency_plan = new isys_import_helper(
                [
                    "id"   => -1,
                    "type" => "C__OBJTYPE__EMERGENCY_PLAN"
                ], $p_emergency_plan_title
            );
        }

        return new isys_import_helper(
            [
                "data_id" => -1
            ], "", [
                "emergency_plan" => $l_emergency_plan,
                "title"          => $p_title,
                "description"    => $p_description
            ]
        );
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_type
     * @param unknown_type $p_unit
     * @param unknown_type $p_capacity
     * @param unknown_type $p_description
     * @param unknown_type $p_additional_description
     *
     * @return unknown
     */
    public function parse_storage($p_title, $p_type = "LC__STORAGE_TYPE__HARD_DISK", $p_unit = 1, $p_capacity = null, $p_description = null, $p_additional_description = null)
    {

        $l_description = $p_description;

        if (!is_null($p_additional_description))
        {
            $l_description .= "\n" . $p_additional_description;
        }

        return new isys_import_helper(
            [
                "data_id" => -1
            ], "", [
                "title"       => $p_title,
                "description" => $l_description,
                "capacity"    => $p_capacity,
                "unit"        => new isys_import_helper(
                    [
                        "title_lang" => $p_unit
                    ], $p_unit
                ),
                "type"        => new isys_import_helper(
                    [
                        "title_lang" => $p_type
                    ], $p_type
                ),
                "unit"        => $p_unit
            ]
        );

    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_type
     * @param unknown_type $p_device_title
     * @param unknown_type $p_description
     * @param unknown_type $p_additional_description
     *
     * @return unknown
     */
    public function parse_controller($p_title, $p_type = "SATA", $p_device_title = null, $p_description = null, $p_additional_description = null)
    {
        $l_description = $p_description;

        if (!is_null($p_additional_description))
        {
            $l_description .= "\n" . $p_additional_description;
        }

        return new isys_import_helper(
            [
                "data_id" => -1
            ], "", [
                "title"       => $p_title,
                "description" => $l_description,
                "type"        => $p_type,
                "device"      => new isys_import_helper(
                    [], "Device", [
                        "title"       => $p_device_title,
                        "description" => $l_description,
                    ]
                )
            ]
        );

    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_mail
     * @param unknown_type $p_tel
     *
     * @return unknown
     */
    public function parse_person_group($p_title, $p_mail, $p_tel)
    {
        return [
            "title"         => $p_title,
            "email_address" => $p_mail,
            "phone"         => $p_tel
        ];
    }

    /**
     * Deprecated.
     *
     * @deprecated
     *
     * @param   string $p_street
     * @param   string $p_zip
     * @param   string $p_city
     *
     * @return  array
     */
    public function parse_building($p_street, $p_zip, $p_city)
    {
        return [
            "street"   => $p_street,
            "postcode" => $p_zip,
            "city"     => $p_city
        ];
    }

    /**
     * Deprecated.
     *
     * @deprecated
     *
     * @param   string $p_floor
     *
     * @return  array
     */
    public function parse_room($p_floor)
    {
        return ["floor" => $p_floor];
    }

    /**
     * Deprecated
     *
     * @deprecated
     *
     * @param   string $p_title
     *
     * @return  isys_import_helper
     */
    public function parse_contact($p_title)
    {
        return new isys_import_helper(
            ["data_id" => -1], "", [
                "contact" => new isys_import_helper(["type" => "C__OBJTYPE__PERSON_GROUP"], $p_title),
                "primary" => 1
            ]
        );
    }

    /**
     * Deprecated.
     *
     * @deprecated
     *
     * @param   integer $p_location
     * @param   integer $p_locationObjType
     * @param   integer $p_position
     * @param   integer $p_insertion
     *
     * @return  array
     */
    public function parse_location($p_location, $p_locationObjType, $p_position, $p_insertion)
    {
        return [
            [
                'properties' => [
                    'parent'    => ['value' => $p_location],
                    'pos'       => ['value' => $p_position],
                    'insertion' => ['value' => $p_insertion],
                ]
            ]
        ];
    } // function

    /**
     * Deprecated
     *
     * @param unknown_type $l_ff
     * @param unknown_type $l_he
     *
     * @return unknown
     */
    public function parse_formfactor($p_ff, $p_he)
    {
        if (!empty($this->m_dialog_cache))
        {
            if (!empty($p_ff)) $l_ff = isys_import_handler::check_dialog('isys_catg_formfactor_type', $p_ff);
            else $l_ff = null;
        }

        return [
            [
                'properties' => [
                    "formfactor" => ['value' => $l_ff],
                    "rackunits"  => ['value' => $p_he]
                ]
            ]
        ];
    } // function

    /**
     * Parses the global category "orga assignment" in an allowed array for the import (C)
     *
     * @param string $p_work
     * @param string $p_workplace_id
     * @param string $p_description
     *
     * @return array
     */
    public function parse_orga_assignment($p_work = null, $p_workplace_id = null, $p_description = null)
    {
        return [
            [
                'properties' => [
                    'work'         => ['value' => $p_work],
                    'workplace_id' => ['value' => $p_workplace_id],
                    'description'  => ['value' => $p_description]
                ]
            ]
        ];
    } // fnuction

    /**
     * Parses the specific category "client" in an allowed array for the import
     *
     * @param string $p_type
     * @param string $p_keyboard_layout
     * @param string $p_description
     *
     * @return array
     */
    public function parse_client($p_type, $p_keyboard_layout, $p_description)
    {

        return [
            [
                'properties' => [
                    'type'            => ['value' => isys_import_handler::check_dialog('isys_client_type', $p_type)],
                    'keyboard_layout' => ['value' => $p_keyboard_layout],
                    'description'     => ['value' => $p_description]
                ]
            ]
        ];
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_type
     * @param unknown_type $p_keyboard_layout
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function _parse_client($p_type, $p_keyboard_layout, $p_description)
    {

        if ($l_cache = $this->get_m_cache("isys_client_type", $p_type)) $p_type = $l_cache;

        return [
            0 => [
                "type"            => new isys_import_helper(
                    [
                        "title" => $p_type
                    ], $p_type
                ),
                "keyboard_layout" => $p_keyboard_layout,
                "description"     => $p_description
            ]
        ];
    }

    /**
     * Parses the global category "leasing" in an allowed array for the import (C)
     *
     *
     * @param string $p_cart_number
     * @param string $p_rent_certificate_number
     * @param string $p_rent_certificate_position
     * @param string $p_costs_per_month
     * @param string $p_end_of_lease
     * @param string $p_description
     *
     * @return array
     */
    public function parse_leasing($p_cart_number, $p_rent_certificate_number, $p_rent_certificate_position, $p_costs_per_month, $p_end_of_lease, $p_description)
    {

        if ($p_end_of_lease != "")
        {
            $l_time_arr = explode(".", $p_end_of_lease);
            $l_date     = mktime(null, null, null, $l_time_arr[1], $l_time_arr[0], $l_time_arr[2]);
        }

        if ($p_costs_per_month != '')
        {
            unset($l_matches);
            preg_match("/^\d+(\,\d{1,2}|\.\d{1,2})?|-/i", $p_costs_per_month, $l_matches);
            $p_costs_per_month = $l_matches[0];
        }

        return [
            [
                'properties' => [
                    'cart_number'               => ['value' => $p_cart_number],
                    'rent_certificate_number'   => ['value' => $p_rent_certificate_number],
                    'rent_certificate_position' => ['value' => $p_rent_certificate_position],
                    'costs_per_month'           => ['value' => $p_costs_per_month],
                    'end_of_lease'              => ['value' => date("d.m.Y", $l_date)],
                    'description'               => ['value' => $p_description]
                ]
            ]
        ];
    }

    /**
     * Parses the global category "hostadress" in an allowed array for the import
     *
     * @todo needs to be updated to version 0.9.9-8
     *
     * @param string $p_net_type
     * @param string $p_net
     * @param string $p_adress
     * @param string $p_mask
     * @param string $p_gateway
     * @param string $p_hostname
     * @param string $p_assignment
     * @param string $p_dns_server
     * @param string $p_dns_domain
     * @param string $p_primary
     * @param string $p_active
     * @param string $p_description
     *
     * @return array
     *
     */
    public function parse_hostadress($p_net_type, $p_net, $p_adress = "0.0.0.0", $p_mask = "0.0.0.0", $p_gateway = 0, $p_hostname, $p_assignment, $p_dns_server, $p_dns_domain = null, $p_primary, $p_active, $p_description)
    {
        global $g_comp_database;

        if (empty($p_net) && empty($p_adress) && empty($p_assignment)) return null;

        $l_dao_ip         = new isys_cmdb_dao_category_g_ip($g_comp_database);
        $l_dao_net        = new isys_cmdb_dao_category_s_net($g_comp_database);
        $l_dao_ip_address = new isys_cmdb_dao_category_s_net_ip_addresses($g_comp_database);

        $l_net_type    = null;
        $l_address     = $p_adress;
        $l_net_obj_id  = null;
        $l_subnet_mask = $p_mask;
        $l_assignment  = null;
        $l_hostname    = $p_hostname;

        // Net Type
        if (!empty($p_net_type))
        {
            if ((stripos($p_net_type, "ipv4") !== false) || (stripos($p_net_type, "ipv 4") !== false))
            {
                // Type is IPV4
                $l_net_type       = C__CATS_NET_TYPE__IPV4;
                $l_net_global_net = C__OBJ__NET_GLOBAL_IPV4;
            }
            elseif ((stripos($p_net_type, "ipv6") !== false) || (stripos($p_net_type, "ipv 6") !== false))
            {
                // Type is IPV6
                $l_net_type       = C__CATS_NET_TYPE__IPV6;
                $l_net_global_net = C__OBJ__NET_GLOBAL_IPV6;
            }
        }
        else
        {
            $l_net_type       = C__CATS_NET_TYPE__IPV4;
            $l_net_global_net = C__OBJ__NET_GLOBAL_IPV4;
        }

        if (is_null($l_net_type))
        {
            // Get type from address
            $l_adress_arr = explode(".", $p_adress);
            if (count($l_adress_arr) == 4)
            {
                // IPV4
                $l_net_type = C__CATS_NET_TYPE__IPV4;
            }
            else
            {
                $l_adress_arr = explode(":", $p_adress);
                if (count($l_adress_arr) > 1)
                {
                    $l_net_type = C__CATS_NET_TYPE__IPV6;
                }
            }
        }

        // Net assignment for the ip
        switch ($l_net_type)
        {
            case C__CATS_NET_TYPE__IPV4:
                if (stripos($p_assignment, 'dhcp') !== false)
                {
                    $l_assignment = C__CATP__IP__ASSIGN__DHCP;
                }
                else
                {
                    if (!empty($p_adress))
                    {
                        $l_assignment = C__CATP__IP__ASSIGN__STATIC;
                    }
                    else
                    {
                        $l_assignment = C__CATP__IP__ASSIGN__UNNUMBERED;
                    }
                }
                if (!empty($l_address))
                {
                    if (!Ip::validate_ip($l_address))
                    {
                        $l_address = '';
                        if ($l_assignment != C__CATP__IP__ASSIGN__UNNUMBERED) $l_assignment = C__CATP__IP__ASSIGN__UNNUMBERED;
                    }
                }

                break;
            case C__CATS_NET_TYPE__IPV6:
                if (stripos($p_assignment, 'dhcp') !== false)
                {
                    $l_assignment = C__CMDB__CATG__IP__DHCPV6;
                }
                else
                {
                    $l_assignment = C__CMDB__CATG__IP__STATIC;
                }
                if (!empty($l_address))
                {
                    $l_address = Ip::validate_ipv6($l_address);
                }

                break;
        }

        // NET
        if (!empty($p_net))
        {

            $l_res_net = $l_dao_net->retrieve("SELECT * FROM isys_obj WHERE isys_obj__title = " . $l_dao_net->convert_sql_text($p_net));
            if ($l_res_net->num_rows() > 0)
            {
                // Check in object table
                $l_row_net    = $l_res_net->get_row();
                $l_net_obj_id = $l_row_net['isys_obj__id'];
            }
            elseif (Ip::validate_ip($p_net) || Ip::validate_ipv6($p_net))
            {
                // Net is a ip
                // Check in specific category as ip
                $l_res_net = $l_dao_net->get_data(null, null, " AND isys_cats_net_list__address = " . $l_dao_net->convert_sql_text($p_net));
                if ($l_res_net->num_rows() > 0)
                {
                    // Entry exists use the id
                    $l_row_net    = $l_res_net->get_row();
                    $l_net_obj_id = $l_row_net['isys_cats_net_list__isys_obj__id'];
                }
            }
        }
        else
        {
            $l_net_obj_id = $l_net_global_net;
        }

        // DNS Server
        $l_dns_server_id = null;
        if (!empty($p_dns_server))
        {
            if (strpos($p_dns_server, ',') > 0)
            {
                $l_arr = explode(',', $p_dns_server);
                foreach ($l_arr AS $l_dns_server)
                {
                    if (Ip::validate_ip($l_dns_server) || Ip::validate_ipv6($l_dns_server))
                    {
                        $l_res_dns_server = $l_dao_ip_address->get_data(
                            null,
                            null,
                            "AND isys_cats_net_ip_addresses_list__title = " . $l_dao_ip->convert_sql_text($l_dns_server)
                        );
                        if ($l_res_dns_server->num_rows() > 0)
                        {
                            $l_row_dns_server   = $l_res_dns_server->get_row();
                            $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                        }
                    }
                    else
                    {
                        // Check if hostname
                        $l_res_dns_server = $l_dao_ip->retrieve(
                            "SELECT isys_catg_ip_list__id FROM isys_catg_ip_list WHERE  isys_catg_ip_list__hostname = " . $l_dao_ip->convert_sql_text($l_dns_server)
                        );
                        if ($l_res_dns_server->num_rows() > 0)
                        {
                            $l_row_dns_server   = $l_res_dns_server->get_row();
                            $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                        }
                        else
                        {
                            // Check if object
                            $l_res_dns_server = $l_dao_ip->retrieve("SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = " . $l_dao_ip->convert_sql_text($l_dns_server));
                            if ($l_res_dns_server->num_rows() > 0)
                            {
                                // It is an object
                                $l_row_dns_server    = $l_res_dns_server->get_row();
                                $l_res_dns_server_ip = $l_dao_ip->get_data(null, $l_row_dns_server['isys_obj__id']);
                                while ($l_row_dns_server = $l_res_dns_server_ip->get_row())
                                {
                                    if ($l_row_dns_server['isys_catg_ip_list__primary'] = 0)
                                    {
                                        $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                if (Ip::validate_ip($p_dns_server) || Ip::validate_ipv6($p_dns_server))
                {
                    $l_res_dns_server = $l_dao_ip_address->get_data(null, null, "AND isys_cats_net_ip_addresses_list__title = " . $l_dao_ip->convert_sql_text($p_dns_server));
                    if ($l_res_dns_server->num_rows() > 0)
                    {
                        $l_row_dns_server   = $l_res_dns_server->get_row();
                        $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                    }
                }
                else
                {
                    // Check if hostname
                    $l_res_dns_server = $l_dao_ip->retrieve(
                        "SELECT isys_catg_ip_list__id FROM isys_catg_ip_list WHERE  isys_catg_ip_list__hostname = " . $l_dao_ip->convert_sql_text($p_dns_server)
                    );
                    if ($l_res_dns_server->num_rows() > 0)
                    {
                        $l_row_dns_server   = $l_res_dns_server->get_row();
                        $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                    }
                    else
                    {
                        // Check if object
                        $l_res_dns_server = $l_dao_ip->retrieve("SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = " . $l_dao_ip->convert_sql_text($p_dns_server));
                        if ($l_res_dns_server->num_rows() > 0)
                        {
                            // It is an object
                            $l_row_dns_server    = $l_res_dns_server->get_row();
                            $l_res_dns_server_ip = $l_dao_ip->get_data(null, $l_row_dns_server['isys_obj__id']);
                            while ($l_row_dns_server = $l_res_dns_server_ip->get_row())
                            {
                                if ($l_row_dns_server['isys_catg_ip_list__primary'] = 0)
                                {
                                    $l_dns_server_ids[] = $l_row_dns_server['isys_catg_ip_list__id'];
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }

        // DNS DOMAIN
        if (!empty($p_dns_domain))
        {
            if (strpos($p_dns_domain, ',') > 0)
            {
                $l_arr = explode(',', $p_dns_domain);
                foreach ($l_arr AS $l_domain)
                {
                    $l_dns_domain_ids[] = isys_import_handler::check_dialog('isys_net_dns_domain', $l_domain);
                }
            }
            else
            {
                $l_dns_domain_ids[] = isys_import_handler::check_dialog('isys_net_dns_domain', $p_dns_domain);
            }
        }

        if (strtolower($p_primary) == 'ja' || strtolower($p_primary) == 'yes' || strtolower($p_primary) == 'j')
        {
            $p_primary = 1;
        }
        else
        {
            $p_primary = 0;
        }

        if (strtolower($p_active) == 'ja' || strtolower($p_active) == 'yes' || strtolower($p_active) == 'j' || strtolower($p_active) == 'aktiv' || strtolower(
                $p_active
            ) == 'active'
        )
        {
            $p_active = 1;
        }
        else
        {
            $p_active = 0;
        }

        if (strtolower($p_gateway) == 'ja' || strtolower($p_gateway) == 'yes' || strtolower($p_gateway) == 'j')
        {
            $p_gw = 1;
        }
        else
        {
            $p_gw = 0;
        }

        $l_return = [
            'properties' => [
                'net_type'             => ['value' => $l_net_type],
                'net'                  => ['value' => $l_net_obj_id],
                'hostname'             => ['value' => $p_hostname],
                'primary'              => ['value' => $p_primary],
                'use_standard_gateway' => ['value' => $p_gw],
                'active'               => ['value' => $p_active],
                'dns_server'           => ['value' => ((empty($l_dns_server_ids)) ? null : $l_dns_server_ids)],
                'dns_domain'           => ['value' => ((empty($l_dns_domain_ids)) ? null : $l_dns_domain_ids)],
                'description'          => ['value' => $p_description]
            ]
        ];

        switch ($l_net_type)
        {
            case C__CATS_NET_TYPE__IPV4:
                $l_return['properties']['ipv4_assignment'] = ['value' => $l_assignment];
                $l_return['properties']['ipv4_address']    = ['value' => $l_address];
                break;
            case C__CATS_NET_TYPE__IPV6:
                $l_return['properties']['ipv6_assignment'] = ['value' => $l_assignment];
                $l_return['properties']['ipv6_address']    = ['value' => $l_address];
                break;
        }

        return [
            $l_return
        ];
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_net_type
     * @param unknown_type $p_net
     * @param unknown_type $p_adress
     * @param unknown_type $p_mask
     * @param unknown_type $p_gateway
     * @param unknown_type $p_hostname
     * @param unknown_type $p_assignment
     * @param unknown_type $p_dns_server
     * @param unknown_type $p_dns_domain
     * @param unknown_type $p_primary
     * @param unknown_type $p_active
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function _parse_hostadress($p_net_type, $p_net, $p_adress = "0.0.0.0", $p_mask = "0.0.0.0", $p_gateway = "0.0.0.0", $p_hostname, $p_assignment, $p_dns_server, $p_dns_domain, $p_primary, $p_active, $p_description)
    {
        $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->m_db);

        if ($p_net != "")
        {
            $l_res = $l_dao_ip->get_data(null, null, " AND isys_obj__title = '" . $p_net . "'");

            if ($l_res->num_rows() > 0)
            {
                $l_row        = $l_res->get_row();
                $l_net_obj_id = $l_row["isys_obj__id"];
            }
            else
            {
                $l_net_obj_id = $l_dao_ip->insert_new_obj(C__OBJTYPE__LAYER3_NET, false, $p_net, null, C__RECORD_STATUS__NORMAL);
            }
        }
        else
        {
            $l_net_obj_id = "";
        }

        if (strtolower($p_primary) == 'ja' || strtolower($p_primary) == 'yes' || strtolower($p_primary) == 'j')
        {
            $p_primary = 1;
        }
        else
        {
            $p_primary = 0;
        }

        if (strtolower($p_active) == 'ja' || strtolower($p_active) == 'yes' || strtolower($p_active) == 'j' || strtolower($p_active) == 'aktiv' || strtolower(
                $p_active
            ) == 'active'
        )
        {
            $p_active = 1;
        }
        else
        {
            $p_active = 0;
        }

        if ($l_cache = $this->get_m_cache("isys_net_type", $p_net_type)) $p_net_type = $l_cache;

        if ($l_cache = $this->get_m_cache("isys_ip_assignment", $p_assignment)) $p_assignment = $l_cache;

        if ($l_cache = $this->get_m_cache("isys_net_dns_server", $p_dns_server)) $p_dns_server = $l_cache;

        if ($l_cache = $this->get_m_cache("isys_net_dns_domain", $p_dns_domain)) $p_dns_domain = $l_cache;

        return [
            0 => [
                "net_type"    => new isys_import_helper(
                    [
                        "title" => $p_net_type
                    ], $p_net_type
                ),
                "net"         => new isys_import_helper(
                    [
                        "id"    => $l_net_obj_id,
                        "title" => $p_net
                    ], $p_net
                ),
                "address"     => $p_adress,
                "mask"        => $p_mask,
                "gateway"     => $p_gateway,
                "hostname"    => $p_hostname,
                "assignment"  => new isys_import_helper(
                    [
                        "title" => $p_assignment
                    ], $p_assignment
                ),
                "dns_server"  => new isys_import_helper(
                    [
                        "title" => $p_dns_server
                    ], $p_dns_server
                ),
                "dns_domain"  => new isys_import_helper(
                    [
                        "title" => $p_dns_domain
                    ], $p_dns_domain
                ),
                "primary"     => $p_primary,
                "active"      => $p_active,
                "description" => $p_description

            ]
        ];
    }

    /**
     * Parses the specific category "monitor" in an allowed array for the import
     *
     * @param string $p_display
     * @param string $p_display_unit
     * @param string $p_type
     * @param string $p_resolution
     * @param string $p_description
     *
     * @return array
     */
    public function parse_monitor($p_display, $p_display_unit, $p_type, $p_resolution, $p_description)
    {

        $l_arr = [
            [
                'properties' => [
                    'size'        => ['value' => $p_display],
                    'size_unit'   => ['value' => isys_import_handler::check_dialog('isys_monitor_unit', $p_display_unit)],
                    'type'        => ['value' => isys_import_handler::check_dialog('isys_monitor_type', $p_type)],
                    'resolution'  => ['value' => isys_import_handler::check_dialog('isys_monitor_resolution', $p_resolution)],
                    'description' => ['value' => $p_description],
                ]
            ]
        ];

        return $l_arr;
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_display
     * @param unknown_type $p_display_unit
     * @param unknown_type $p_type
     * @param unknown_type $p_resolution
     * @param unknown_type $p_description
     *
     * @return unknown
     */
    public function _parse_monitor($p_display, $p_display_unit, $p_type, $p_resolution, $p_description)
    {

        if ($l_cache = $this->get_m_cache("isys_monitor_unit", $p_display_unit)) $p_display_unit = $l_cache;

        if ($l_cache = $this->get_m_cache("isys_monitor_type", $p_type)) $p_type = $l_cache;

        if ($l_cache = $this->get_m_cache("isys_monitor_resolution", $p_resolution)) $p_resolution = $l_cache;

        return [
            0 => [
                "size"        => $p_display,
                "size_unit"   => new isys_import_helper(
                    [
                        "title" => $p_display_unit
                    ], $p_display_unit
                ),
                "type"        => new isys_import_helper(
                    [
                        "title" => $p_type
                    ], $p_type
                ),
                "resolution"  => new isys_import_helper(
                    [
                        "title" => $p_resolution
                    ], $p_resolution
                ),
                "description" => $p_description
            ]
        ];
    }

    /**
     * Parses the global category "scan" in an allowed array for the import (C)
     *
     * @param string $p_scanned_date
     * @param string $p_scanned_time
     * @param string $p_scan__id
     * @param string $p_connected_obj_title
     *
     * @return array
     */
    public function parse_scan($p_scanned_date = null, $p_scanned_time = null, $p_scan__id = null, $p_connected_obj_title = null)
    {

        if ($p_scanned_date != "")
        {
            $l_time_arr = explode(".", $p_scanned_date);
            $l_date     = mktime(null, null, null, $l_time_arr[1], $l_time_arr[0], $l_time_arr[2]);
        }

        return [
            [
                'properties' => [
                    'acquisition_submitted' => ["value" => date("d.m.Y", $l_date)],
                    'acquisition_time'      => ["value" => $p_scanned_time],
                    'scan_id'               => ["value" => $p_scan__id],
                    'connected_obj_title'   => ["value" => $p_connected_obj_title]
                ]
            ]
        ];
    }

    public function parse_cats_contract($p_contract_type = null, $p_contract_no = null, $p_customer_no = null, $p_internal_no = null, $p_costs = null, $p_product = null, $p_reaction_time = null, $p_contract_status = null, $p_contract_start = null, $p_contract_end = null, $p_contract_runtime = null, $p_contract_runtime_unit = null, $p_contract_ends_by = null, $p_contract_notice_date = null, $p_contract_notice_period = null, $p_contract_notice_period_unit = null, $p_contract_notice_type = null, $p_contract_guarantee_period = null, $p_contract_guarantee_period_unit = null, $p_contract_description = null)
    {

        if (p_contract_type) $l_contract_type_id = $this->get_dialog_cache('isys_contract_type', $p_contract_type);
        else $l_contract_type_id = null;

        if ($p_reaction_time) $l_reaction_time_id = $this->get_dialog_cache('isys_contract_reaction_rate', $p_reaction_time);
        else $l_reaction_time_id = null;

        if ($p_contract_status) $l_contract_status_id = $this->get_dialog_cache('isys_contract_status', $p_contract_status);
        else $l_contract_status_id = null;

        if ($p_contract_runtime_unit) $l_contract_runtime_unit = $this->get_dialog_cache('isys_guarantee_period_unit', $p_contract_runtime_unit);
        else $l_contract_runtime_unit = null;

        if ($p_contract_ends_by) $l_contract_ends_by = $this->get_dialog_cache('isys_contract_end_type', $p_contract_ends_by);
        else $l_contract_ends_by = null;

        if ($p_contract_notice_period_unit) $l_contract_notice_period_unit = $this->get_dialog_cache('isys_guarantee_period_unit', $p_contract_notice_period_unit);
        else $l_contract_notice_period_unit = null;

        if ($p_contract_notice_type) $l_contract_notice_type = $this->get_dialog_cache('isys_contract_notice_period_type', $p_contract_notice_type);
        else $l_contract_notice_type = null;

        if ($p_contract_guarantee_period_unit) $l_contract_guarantee_period_unit = $this->get_dialog_cache('isys_guarantee_period_unit', $p_contract_guarantee_period_unit);
        else $l_contract_guarantee_period_unit = null;

        return [
            [
                'properties' => [
                    'type'                    => ['value' => $l_contract_type_id],
                    'contract_no'             => ['value' => $p_contract_no],
                    'customer_no'             => ['value' => $p_customer_no],
                    'internal_no'             => ['value' => $p_internal_no],
                    'costs'                   => ['value' => $p_costs],
                    'product'                 => ['value' => $p_product],
                    'reaction_rate'           => ['value' => $l_reaction_time_id],
                    'contract_status'         => ['value' => $l_contract_status_id],
                    'start_date'              => ['value' => $p_contract_start],
                    'end_date'                => ['value' => $p_contract_end],
                    'run_time'                => ['value' => $p_contract_runtime],
                    'run_time_unit'           => ['value' => $l_contract_runtime_unit],
                    'end_type'                => ['value' => $l_contract_ends_by],
                    'notice_date'             => ['value' => $p_contract_notice_date],
                    'notice_period'           => ['value' => $p_contract_notice_period],
                    'notice_period_unit'      => ['value' => $l_contract_notice_period_unit],
                    'notice_type'             => ['value' => $l_contract_notice_type],
                    'maintenance_period'      => ['value' => $p_contract_guarantee_period],
                    'maintenance_period_unit' => ['value' => $l_contract_guarantee_period_unit],
                    'description'             => ['value' => $p_contract_description]
                ]
            ]
        ];
    }

    /**
     * Parse application assignment method.
     *
     * @param   string  $p_application
     * @param   integer $p_licence
     *
     * @return  array
     */
    public function parse_application_assignment($p_application = null, $p_licence = null)
    {
        $l_dao         = new isys_cmdb_dao($this->m_db);
        $l_application = $l_dao->get_obj_id_by_title($p_application);

        if (empty($l_application))
        {
            $l_application = $l_dao->insert_new_obj(C__OBJTYPE__APPLICATION, false, $p_application, null, C__RECORD_STATUS__NORMAL);
        } // if

        return [
            [
                'properties' => [
                    'application'              => ['value' => $l_application],
                    'assigned_license'         => ['value' => $p_licence],
                    'assigned_database_schema' => ['value' => null],
                    'assigned_it_service'      => ['value' => null],
                    'description'              => ['value' => null]
                ]
            ]
        ];
    }

    public function parse_virtual_machine($p_virtual_machine = false, $p_runs_on = null, $p_system = null, $p_config_file = null, $p_primary = null, $p_description = null)
    {

        if ($p_virtual_machine) $l_virtual_machine = C__VM__GUEST;
        else $l_virtual_machine = C__VM__NO;

        if ($p_system) $l_system = $this->get_dialog_cache('isys_vm_type', $p_system);
        else $l_system = null;

        return [
            [
                'properties' => [
                    'virtual_machine' => ['value' => $l_virtual_machine],
                    'hosts'           => ['value' => $p_runs_on],
                    'system'          => ['value' => $l_system],
                    'config_file'     => ['value' => $p_config_file],
                    'primary'         => ['value' => $p_primary],
                    'description'     => ['value' => $p_description]
                ]
            ]
        ];

    }

    /**
     *
     * @param   integer $p_connected_object
     * @param   string  $p_contract_start
     * @param   string  $p_contract_end
     * @param   string  $p_description
     *
     * @return  array
     */
    public function parse_contract($p_connected_object = null, $p_contract_start = null, $p_contract_end = null, $p_description = null)
    {
        return [
            [
                'properties' => [
                    'contract_start'     => ['value' => $p_contract_start],
                    'contract_end'       => ['value' => $p_contract_end],
                    'connected_contract' => ['value' => $p_connected_object],
                    'description'        => ['value' => $p_description]
                ]
            ]
        ];
    }

    /**
     * Deprecated
     *
     * @param unknown_type $p_title
     * @param unknown_type $p_object_type
     * @param unknown_type $p_parent
     * @param unknown_type $p_parent_type
     *
     * @return unknown
     */
    public function import_location($p_title, $p_object_type = C__OBJTYPE__ENCLOSURE, $p_parent = null, $p_parent_type = C__OBJTYPE__ROOM)
    {
        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_room_id = $l_dao->get_obj_id_by_title($p_title);
        if (!is_numeric($l_room_id))
        {
            $l_room_id = $l_dao->insert_new_obj(
                $p_object_type,
                false,
                $this->trim_string($p_title),
                null,
                C__RECORD_STATUS__NORMAL
            );
        }

        if (!is_null($p_parent))
        {
            $l_dist = new isys_cmdb_dao_distributor($this->m_db, $l_room_id, C__CMDB__CATEGORY__TYPE_GLOBAL);
            $l_dao  = $l_dist->get_category(C__CATG__LOCATION);

            $l_parent_id = $this->import_location($p_parent, $p_parent_type);

            $l_parent_helper = new isys_import_helper(
                [
                    "id"   => $l_parent_id,
                    "type" => $p_parent_type
                ], $p_parent
            );

            $l_data[0]["parent"] = $l_parent_helper;

            $l_dao->sync($l_data, $l_room_id);

            $l_parent_helper_2     = new isys_import_helper(
                [
                    "id"   => "1",
                    "type" => C__OBJTYPE__LOCATION_GENERIC
                ], "Root-Location"
            );
            $l_data_2[0]["parent"] = $l_parent_helper_2;

            $l_dao->sync($l_data_2, $l_parent_id);

        }

        return $l_room_id;
    } // function

    /**
     * @desc Import parsed data
     *
     * @param string[] $p_data
     * @param int      $p_objtype_id
     *
     * @return bool
     */
    public function import($p_objtype_id, $p_force_overwrite = null, $p_object_id = null)
    {
        global $g_comp_template_language_manager;

        if (is_array($this->m_data))
        {

            /* Get main dao */
            $l_dao = new isys_cmdb_dao($this->m_db);

            /* Unset first csv line if it's just a header */
            if ($this->m_discard_fist_line)
            {
                $this->set_head($this->m_data[0]);
                $this->set_mapping();
                if ($this->m_discarded_lines > 1)
                {
                    for ($i = 0;$i < $this->m_discarded_lines;$i++)
                    {
                        unset($this->m_data[$i]);
                    }
                }
                else
                    unset($this->m_data[0]);
            }
            $this->m_current_line = $this->m_discarded_lines;
            /* Iterate through csv content */
            foreach ($this->m_data as $l_line => $l_content)
            {
                $this->m_current_line++;
                $l_data = null;

                if (is_array($l_content) && !empty($l_content[0]))
                {

                    if (!$this->import_specific_check_before_sync($l_content)) continue;

                    /* Get object title*/
                    if ($this->m_obj_id_column)
                    {
                        /* Get object title*/
                        $l_object_id    = $l_content[$this->m_obj_id_column];
                        $l_object_title = $this->m_object_title_additional . $l_dao->get_obj_name_by_id_as_string($l_object_id);
                        $l_object_type  = $l_dao->get_objTypeID($l_object_id);
                    }
                    else
                    {
                        /* Get object title*/
                        $l_object_id    = null;
                        $l_object_title = $this->m_object_title_additional . trim(trim(trim($l_content[$this->m_title_column]), "'"), "\"");
                        $l_object_type  = null;
                    }

                    if (is_null($l_object_title) || $l_object_title == "")
                    {
                        verbose(C__COLOR__LIGHT_RED . "Object title not found or empty - line: " . ($l_line + 1) . ", column " . $this->m_title_column . C__COLOR__NO_COLOR);
                        continue;
                    }

                    /* Check if object of the same name or type already exists */
                    if (!$this->m_identifier)
                    {
                        switch ($this->m_exist_check)
                        {
                            case self::c__exist_check__sysid_object:
                                $l_object_sysid = $this->trim_string($l_content[$this->m_sysid_column]);
                                if (empty($l_object_sysid)) $l_object_id = $l_dao->get_obj_id_by_title($l_object_title);
                                else
                                    $l_object_id = $l_dao->get_obj_id_by_sysid($l_object_sysid);

                                break;
                            case self::c__exist_check__sysid:
                                $l_object_sysid = trim($l_content[$this->m_sysid_column]);
                                $l_object_id    = $l_dao->get_obj_id_by_sysid($l_object_sysid);
                                break;
                            case self::c__exist_check__object_by_type:
                                if ($this->m_type_column)
                                {
                                    $l_object_type = trim($l_content[$this->m_type_column]);
                                    if (!is_numeric($l_object_type))
                                    {
                                        if (defined($l_object_type)) $l_object_type = constant($l_object_type);
                                    }
                                }
                                elseif ($this->m_default_type)
                                {
                                    $l_object_type = $this->m_default_type;
                                }
                                $l_object_id = $l_dao->get_obj_id_by_title($l_object_title, $l_object_type);
                                break;
                            case self::c__exist_check__import_specified:
                                $l_object_id = $this->import_specific_object_check($l_content);
                                break;
                            case self::c__exist_check__object:
                            default:
                                $l_object_id = $l_dao->get_obj_id_by_title($l_object_title);
                                break;
                        }

                    }
                    else
                    {
                        $l_object_id = $this->check_object_with_identifier($l_content);
                    }

                    /* Get object type */
                    if (is_null($l_object_type))
                    {
                        if (defined($l_content[$this->m_type_column]))
                        {
                            if (!is_numeric($l_content[$this->m_type_column])) $l_object_type = constant(trim($l_content[$this->m_type_column]));
                            else
                                $l_object_type = $l_content[$this->m_type_column];
                        }
                        elseif (array_key_exists(trim(strtolower($l_content[$this->m_type_column]), "'"), $this->m_objtype_map))
                        {
                            $l_object_type = $this->m_objtype_map[trim(strtolower($l_content[$this->m_type_column]), "'")];
                        }
                        elseif ($l_object_id && !$l_object_type)
                        {
                            $l_object_type = $l_dao->get_objTypeID($l_object_id);
                        }
                        elseif (!$l_object_type)
                        {
                            $l_object_type = $this->m_default_type;
                        }
                    }

                    $_GET[C__CMDB__GET__OBJECT] = $l_object_id;
                    isys_module_request::get_instance()
                        ->_internal_set_private("m_get", $_GET);

                    if (!$l_object_id)
                    {
                        if ($this->m_create_new_objects)
                        {
                            /* ---------------------------------------------------------------------------------- */
                            /* Creating object */
                            /* ---------------------------------------------------------------------------------- */
                            $l_object_id = $l_dao->insert_new_obj(
                                $l_object_type,
                                false,
                                $l_object_title,
                                ISYS_NULL,
                                C__RECORD_STATUS__NORMAL,
                                ISYS_NULL
                            );

                            $_GET[C__CMDB__GET__OBJECT] = $l_object_id;

                            verbose(
                                "Object " . $l_object_title . " (" . $l_object_id . ") of type " . _L(
                                    $l_dao->get_objtype_name_by_id_as_string($l_object_type)
                                ) . " successfully created. Importing data.."
                            );
                            isys_import_log::add(
                                "New object " . $l_object_title . " of type " . $g_comp_template_language_manager->get(
                                    $l_dao->get_objtype_name_by_id_as_string($l_object_type)
                                ) . " with id " . $l_object_id . " created"
                            );

                            if ($l_object_id > 0) $this->format_row($l_content, $l_data, $l_object_id);
                        }
                        else
                        {
                            verbose("Line " . $this->m_current_line . " in importfile has been ignored. Object does not exist.");
                            isys_import_log::add("Line " . $this->m_current_line . " in importfile has been ignored. Object does not exist");
                        }
                    }
                    else
                    {
                        if ($p_force_overwrite || $this->m_overwrite)
                        {

                            $_GET[C__CMDB__GET__OBJECT] = $l_object_id;

                            verbose("Object \"{$l_object_title}\" exists.. Overwriting..");
                            if ($this->m_update_object)
                            {
                                $l_dao->update_object($l_object_id, $l_object_type, $l_object_title);
                                verbose("Updating object.");
                            }
                            else
                            {
                                verbose("Skipping object update.");
                            }

                            if ($l_object_id > 0) $this->format_row($l_content, $l_data, $l_object_id);
                        }
                        else
                        {
                            verbose("Object already exists.");
                        }
                    }

                    /* Call sync for each category */
                    if (is_array($l_data) && !empty($l_object_id))
                    {

                        foreach ($l_data as $l_cat_type => $l_cat_data)
                        {

                            try
                            {
                                /* Get distributor */
                                $l_dist = new isys_cmdb_dao_distributor($this->m_db, $l_object_id, $l_cat_type);

                                if ($l_dist && $l_dist->count() > 0)
                                {
                                    if (is_array($l_cat_data))
                                    {

                                        foreach ($l_cat_data as $l_cat_id => $l_sync_data)
                                        {
                                            if (is_array($l_sync_data) && count($l_sync_data) > 0)
                                            {

                                                try
                                                {

                                                    $this->sync($l_cat_id, $l_sync_data, $l_object_id, $l_dist, $l_cat_type);
                                                }
                                                catch (Exception $e)
                                                {
                                                    throw $e;
                                                }
                                            }
                                        }

                                        verbose(C__COLOR__LIGHT_GREEN . " done" . C__COLOR__NO_COLOR, false);

                                    }
                                }
                                unset($l_dist);
                            }
                            catch (Exception $e)
                            {
                                verbose($e->getMessage());
                            }
                        }
                        $this->additional_import($l_object_id, $l_content);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Trims the string
     *
     * @param string $p_string
     * @param string $p_trimmer
     *
     * @return string
     */
    public function trim_string($p_string, $p_trimmer = "")
    {
        if ($p_trimmer != "") return trim($p_string, $p_trimmer);
        else
            return trim(trim($p_string, "'"), "\"");
    } // function

    /**
     * Sets header in member variable.
     *
     * @param  array $p_arr
     */
    public function set_head($p_arr)
    {
        $this->m_csv_head = $p_arr;
    }

    /**
     * Gets header.
     *
     * @return  array
     */
    public function get_head()
    {
        return $this->m_csv_head;
    }

    /**
     * Checks if object exists with the member variable m_identifier it is also possible to check other values in other categories
     *
     * @param   array $p_content
     *
     * @return  mixed
     */
    protected function check_object_with_identifier($p_content)
    {
        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_ident = $this->m_object_title_additional . $this->trim_string($p_content[$this->m_identifier], "'");

        $l_id = $l_dao->retrieve("SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = " . $l_dao->convert_sql_text($l_ident) . ";")
            ->get_row_value('isys_obj__id');

        if ($l_id && $l_id > 0)
        {
            return $l_id;
        } // if

        return false;
    }

    /**
     * Deprecated
     *
     * @param int    $p_category_id
     * @param array  $p_data
     * @param int    $p_object_id
     * @param object $p_dist
     * @param int    $p_cat_type
     *
     * @return mixed_var
     */
    protected function _sync($p_category_id, $p_data, $p_object_id, $p_dist = null, $p_cat_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {
        try
        {

            if (!is_object($p_dist))
            {
                $p_dist = new isys_cmdb_dao_distributor($this->m_db, $p_object_id, $p_cat_type);
            }

            if (is_object($p_dist))
            {
                $l_overview = new isys_cmdb_dao_category_g_overview($this->m_db);

                $l_catg = $p_dist->get_category($p_category_id);

                /**
                 * @var $l_catg_dao isys_cmdb_dao_category
                 */
                if ($p_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL)
                {
                    $l_catg_dao = $l_overview->get_dao_by_catg_id($p_category_id);
                }
                else
                {
                    $l_catg_dao = $l_overview->get_dao_by_cats_id($p_category_id);
                }

                if ($p_category_id != C__CATG__GLOBAL)
                {
                    $l_isysgui = $p_dist->get_guidata($p_category_id);
                    $l_ctype   = $p_dist->resolve_disttype($p_cat_type);
                    $l_table   = $l_isysgui["isysgui_cat{$l_ctype}__source_table"];

                    if ($l_isysgui["isysgui_cat{$l_ctype}__list_multi_value"] == "1")
                    {
                        $l_catg_dao->clear_data($p_object_id, $l_table);
                        //$l_catg_dao->get_general_data();
                    }

                }

                if (is_object($l_catg_dao))
                {
                    // Initialize category
                    $l_catg_dao->init($l_catg->get_result());

                    if (method_exists($l_catg_dao, "sync"))
                    {
                        if ($l_catg_dao->sync($p_data, $p_object_id, isys_import_handler_cmdb::C__CREATE))
                        {
                            unset($l_catg);
                            unset($l_catg_dao);
                            unset($l_overview);
                            unset($l_isysgui);

                            return true;
                        }
                    }
                }
                else
                {
                    verbose("Could not initialize category: " . $p_category_id . "", true, "!");
                }

                unset($l_catg);
                unset($l_catg_dao);
                unset($l_overview);
                unset($l_isysgui);

                return false;
            }
            else throw new Exception("Could not resolve distributor for category id: " . $p_category_id);

        }
        catch (Exception $e)
        {
            throw $e;
        }

    }

    /**
     * New sync method for version 0.9.9-7 or above
     *
     * @param int    $p_category_id
     * @param array  $p_data
     * @param int    $p_object_id
     * @param object $p_dist
     * @param int    $p_cat_type
     *
     * @return bool
     */
    protected function sync($p_category_id, $p_data, $p_object_id, $p_dist = null, $p_cat_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {

        global $g_comp_template_language_manager;

        try
        {

            if (!is_object($p_dist))
            {
                $p_dist = new isys_cmdb_dao_distributor($this->m_db, $p_object_id, $p_cat_type);
            }

            if (is_object($p_dist))
            {
                $l_overview = new isys_cmdb_dao_category_g_overview($this->m_db);

                /**
                 * @var $l_catg_dao isys_cmdb_dao_category
                 */
                $l_catg = $p_dist->get_category($p_category_id);

                if ($p_category_id == C__CATS__PERSON_MASTER) $p_category_id = C__CATS__PERSON;

                if ($p_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL) $l_catg_dao = $l_overview->get_dao_by_catg_id($p_category_id);
                else
                    $l_catg_dao = $l_overview->get_dao_by_cats_id($p_category_id);

                $l_isysgui    = $p_dist->get_guidata($p_category_id);
                $l_ctype      = $p_dist->resolve_disttype($p_cat_type);
                $l_table      = $l_isysgui["isysgui_cat{$l_ctype}__source_table"];
                $l_multivalue = false;

                if ($l_isysgui["isysgui_cat{$l_ctype}__list_multi_value"] == "1")
                {
                    $l_catg_dao->clear_data($p_object_id, $l_table);
                    $l_multivalue = true;
                }
                $l_table = (!strpos($l_isysgui["isysgui_cat{$l_ctype}__source_table"], '_list') && !strpos(
                        $l_isysgui["isysgui_cat{$l_ctype}__source_table"],
                        '_2_'
                    )) ? $l_isysgui["isysgui_cat{$l_ctype}__source_table"] . '_list' : $l_isysgui["isysgui_cat{$l_ctype}__source_table"];

                if (is_object($l_catg_dao))
                {
                    /* Initialize category */
                    $l_catg_dao->init($l_catg->get_result());

                    if (method_exists($l_catg_dao, "sync"))
                    {

                        if ($l_multivalue)
                        {
                            // Multivalue
                            foreach ($p_data AS $l_data)
                            {
                                $l_catg_dao->sync($l_data, $p_object_id, isys_import_handler_cmdb::C__CREATE);
                            }

                        }
                        else
                        {

                            $l_row = $l_catg_dao->get_data(null, $p_object_id)
                                ->get_row();

                            if ($l_row)
                            {
                                $p_data[0]['data_id'] = $l_row[$l_table . "__id"];
                                $l_status             = isys_import_handler_cmdb::C__UPDATE;
                            }
                            else
                            {
                                $l_status = isys_import_handler_cmdb::C__CREATE;
                            }

                            // singlevalue
                            if ($l_catg_dao->sync($p_data[0], $p_object_id, $l_status))
                            {

                                unset($l_catg);
                                unset($l_catg_dao);
                                unset($l_overview);
                                unset($l_isysgui);

                                return true;
                            }
                        }

                        // Emit category signal (afterCategoryEntrySave).
                        isys_component_signalcollection::get_instance()
                            ->emit(
                                "mod.cmdb.afterCategoryEntrySave",
                                $l_catg_dao,
                                isset($p_data[0]['data_id']) ? $p_data[0]['data_id'] : null,
                                true,
                                $p_object_id,
                                $p_data,
                                []
                            );
                    }
                }
                else
                {
                    verbose("Could not initialize category: " . $p_category_id . "", true, "!");
                }

                unset($l_catg);
                unset($l_catg_dao);
                unset($l_overview);
                unset($l_isysgui);

                return false;
            }
            else throw new Exception("Could not resolve distributor for category id: " . $p_category_id);

        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Sets all dialog tables in cache for the current csv import
     *
     * @param array $p_arr
     *
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function set_dialog_cache($p_arr = [])
    {
        global $g_comp_database;

        if (count($p_arr) > 0)
        {
            $l_dao = new isys_cmdb_dao($g_comp_database);
            foreach ($p_arr AS $l_dialog_table)
            {
                $l_res = $l_dao->get_dialog($l_dialog_table);
                while ($l_row = $l_res->get_row())
                {
                    $this->m_dialog_cache[$l_dialog_table][_L($l_row[$l_dialog_table . '__title'])] = $l_row[$l_dialog_table . '__id'];
                    if (strpos($l_row[$l_dialog_table . '__title'], 'LC_') >= 0 && strpos(
                            $l_row[$l_dialog_table . '__title'],
                            'LC_'
                        ) !== false
                    ) $this->m_dialog_cache[$l_dialog_table][$l_row[$l_dialog_table . '__title']] = $l_row[$l_dialog_table . '__id'];
                }
            }
        }
    }

    /**
     * Gets the id from the specified dialog table and value
     *
     * @param $p_table
     * @param $p_value
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function get_dialog_cache($p_table, $p_value)
    {
        global $g_comp_database;

        if (array_key_exists($p_table, $this->m_dialog_cache))
        {
            if (array_key_exists($p_value, $this->m_dialog_cache[$p_table]))
            {
                return $this->m_dialog_cache[$p_table][$p_value];
            }
            else
            {
                $l_dialog_admin                           = new isys_cmdb_dao_dialog_admin($g_comp_database);
                $l_id                                     = $l_dialog_admin->create($p_table, $p_value, null, null, C__RECORD_STATUS__NORMAL);
                $this->m_dialog_cache[$p_table][$p_value] = $l_id;

                return $l_id;
            }
        }
    } // function

    /**
     * Does nothing.
     * Can be used for customer specified retrieval of the object id.
     * Cannot be defined abstract because not every customer csv import filter has this method defined.
     */
    protected function import_specific_object_check($p_content = null)
    {
        return true;
    }

    /**
     * Does nothing
     * Can be used for additional customer import procedures.
     * Cannot be defined abstract because not every customer csv import filter has this method defined.
     */
    protected function additional_import($p_obj_id = null, $p_content = null)
    {
        return;
    }

    /**
     * Does nothing.
     * Can be used for a customer as a specified check function which determines if current line should be processed
     * or not.
     * Cannot be defined abstract because not every customer csv import filter has this method defined.
     */
    protected function import_specific_check_before_sync($p_content = null)
    {
        return true;
    }

    /**
     * Does nothing.
     * Can be used for a customer as generating a mapping.
     * Cannot be defined abstract because not every customer csv import filter has this method defined.
     */
    protected function set_mapping()
    {
        ;
    } // function

    /**
     * @todo needs to be updated
     *
     * @Deprecated
     */
    private function set_m_cache()
    {
        global $g_comp_template_language_manager;

        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_sql = "SELECT isys_property_2_cat__property1_reference FROM isys_property_2_cat WHERE isys_property_2_cat__property1_reference IS NOT NULL " . "AND isys_property_2_cat__property1_reference != 'isys_connection' " . "AND isys_property_2_cat__property1_reference != 'isys_logbook' " . "AND isys_property_2_cat__property1_reference != 'isys_role' " . "AND isys_property_2_cat__property1_reference != 'isys_right' " . "AND isys_property_2_cat__property1_reference != 'isys_obj' " . "AND isys_property_2_cat__property1_reference != 'isys_user_setting' " . "AND isys_property_2_cat__property1_reference != 'isys_search' ";

        $l_res = $l_dao->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {

            $l_sql_property = "SELECT * FROM " . $l_row["isys_property_2_cat__property1_reference"];
            $l_res_property = $l_dao->retrieve($l_sql_property);

            while ($l_row_property = $l_res_property->get_row())
            {

                if (strpos($l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__title"], "LC_") !== false)
                {
                    $l_title = $g_comp_template_language_manager->get($l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__title"]);
                    $l_lc    = $l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__title"];
                }
                else
                {
                    $l_title = $l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__title"];
                    $l_lc    = $l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__title"];
                }

                $this->m_cache_arr[$l_row["isys_property_2_cat__property1_reference"]][$l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__id"]] = [
                    "title"       => trim($l_title),
                    "description" => trim($l_row_property[$l_row["isys_property_2_cat__property1_reference"] . "__description"]),
                    "lc"          => $l_lc
                ];
            }
        }
    } // function

    /**
     *
     * @deprecated
     * @todo update to new version
     *
     * @param   string $p_table
     * @param   string $p_compare
     *
     * @return  bool|int
     */
    private function get_m_cache($p_table, $p_compare)
    {
        if ($p_compare == "")
        {
            return;
        } // if

        if (count($this->m_cache_arr[$p_table]) > 0)
        {
            foreach ($this->m_cache_arr[$p_table] AS $l_type__id => $l_type_content)
            {
                foreach ($l_type_content AS $l_key => $l_type_element)
                {
                    if (strpos(strtolower($l_type_element), strtolower($p_compare)) !== false)
                    {
                        return $l_type_content["lc"];
                    } // if
                } // foreach
            } // foreach
        }
        else
        {
            return isys_import_handler::check_dialog($p_table, $p_compare);
        } // if

        return false;
    } // function

    /**
     * Constructor
     *
     * @param  isys_log $p_log
     */
    public function __construct($p_log)
    {
        global $g_comp_database;

        parent::__construct($p_log, $g_comp_database);
    } //function
} // class