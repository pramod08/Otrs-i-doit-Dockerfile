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
 * DAO: assigned nagios services
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_Lists
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_nagios_refs_services extends isys_cmdb_dao_list
{
    /**
     * Returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__NAGIOS_REFS_SERVICES;
    } // function

    /**
     * Returns the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_result($p_str = null, $p_objID, $p_unused = null)
    {
        $l_sql = 'SELECT isys_obj.*, isys_catg_nagios_service_def_list.*, "-" AS inherited, isys_catg_nagios_refs_services_list__isys_obj__id__host AS "host_obj_id"
			FROM isys_catg_nagios_refs_services_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__service
			LEFT JOIN isys_catg_nagios_service_def_list ON isys_catg_nagios_service_def_list__isys_obj__id = isys_obj__id
			WHERE isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id($p_objID) . '
			UNION
			SELECT isys_obj.*, isys_catg_nagios_service_def_list.*, isys_connection__isys_obj__id AS inherited, isys_catg_application_list__isys_obj__id AS "host_obj_id"
			FROM isys_catg_application_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			INNER JOIN isys_catg_nagios_refs_services_list ON isys_catg_nagios_refs_services_list__isys_obj__id__host = isys_connection__isys_obj__id
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__service
			LEFT JOIN isys_catg_nagios_service_def_list ON isys_catg_nagios_service_def_list__isys_obj__id = isys_obj__id
			WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			AND isys_catg_application_list__bequest_nagios_services = 1
			AND isys_catg_application_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Modify row method will be called by each iteration.
     *
     * @param   array $p_row
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function modify_row(&$p_row)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_db);

        $p_row['isys_obj__title'] = '<a href="' . isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_row['isys_obj__id']]) . '">' . $p_row['isys_obj__title'] . '</a>';

        if (!empty($p_row['isys_catg_nagios_service_def_list__check_command']))
        {
            $l_command      = isys_component_dao_nagios::instance($this->m_db)
                ->getCommand($p_row['isys_catg_nagios_service_def_list__check_command']);
            $p_row['check'] = $l_command['name'];
        }
        else if (!empty($p_row['isys_catg_nagios_service_def_list__check_command_plus']))
        {
            $l_command      = $l_dao->get_dialog('isys_nagios_commands_plus', $p_row['isys_catg_nagios_service_def_list__check_command_plus'])
                ->get_row();
            $p_row['check'] = $l_command['isys_nagios_commands_plus__title'];
        } // if

        $p_row['export'] = '<img src="images/icons/silk/tick.png" class="vam mr5" /><span class="vam green">' . _L('LC__UNIVERSAL__YES') . '</span>';

        if (is_numeric($p_row['inherited']))
        {
            $l_inheritance_sql = 'SELECT *
				FROM isys_catg_nagios_service_inheritance
				WHERE isys_catg_nagios_service_inheritance__host__isys_obj__id = ' . $this->convert_sql_id($p_row['host_obj_id']) . '
				AND isys_catg_nagios_service_inheritance__service__isys_obj__id = ' . $this->convert_sql_id($p_row['isys_obj__id']) . ';';

            if (!count($this->retrieve($l_inheritance_sql)))
            {
                $p_row['export'] = '<button type="button" class="btn btn-mini green" onclick="window.toggle_nagios_service_inheritance(this, ' . $p_row['host_obj_id'] . ', ' . $p_row['isys_obj__id'] . ');">
					<img src="images/icons/silk/tick.png" class="mr5" /><span>' . _L('LC__UNIVERSAL__YES') . '</span>
					</button>';
            }
            else
            {
                $p_row['export'] = '<button type="button" class="btn btn-mini red" onclick="window.toggle_nagios_service_inheritance(this, ' . $p_row['host_obj_id'] . ', ' . $p_row['isys_obj__id'] . ');">
					<img src="images/icons/silk/cross.png" class="mr5" /><span>' . _L('LC__UNIVERSAL__NO') . '</span>
					</button>';
            } // if

            $l_inherited_object_title = $l_dao->get_obj_name_by_id_as_string($p_row['inherited']) . ' (' . _L($l_dao->get_obj_type_name_by_obj_id($p_row['inherited'])) . ')';
            $p_row['inherited']       = '<a href="' . isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_row['inherited']]) . '"> ' . _L(
                    'LC__CMDB__NAGIOS_REFS_NAGIOS_SERVICE__INHERITED_FROM',
                    $l_inherited_object_title
                ) . ' </a>';
        } // if
    } // function

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Build header for the list.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_obj__title' => 'LC__OBJTYPE__NAGIOS_SERVICE',
            'check'           => 'LC__CMDB__NAGIOS_REFS_NAGIOS_SERVICE__CHECK',
            'inherited'       => 'LC__CMDB__NAGIOS_REFS_NAGIOS_SERVICE__INHERITED_FROM_SOFTWARE',
            'export'          => 'LC__CMDB__NAGIOS_REFS_NAGIOS_SERVICE__WILL_BE_EXPORT'
        ];
    } // function

    /**
     * Returns the link the browser shall follow if clicked on a row.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function make_row_link()
    {
        return '#';
    } // function
} // class