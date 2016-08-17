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
 * DAO: global service category for Check_MK hosts.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_category_g_cmk_host_service extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cmk_host_service';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'service';
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * This method will return all objects, which use the given service.
     *
     * @param   string $p_service
     * @param   string $p_hostname
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_object_by_service($p_service, $p_hostname = null)
    {
        isys_check_mk_helper::init();

        $l_return = [];
        $l_sql    = 'SELECT * FROM isys_obj
			LEFT JOIN isys_catg_cmk_host_service_list ON isys_catg_cmk_host_service_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE  isys_catg_cmk_host_service_list__service = ' . $this->convert_sql_text($p_service) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_cmk_host_service_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			GROUP BY isys_obj__id;';

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                // If the given hostname does not match with the current one, it's not the right host.
                if ($p_hostname !== null && isys_monitoring_helper::render_export_hostname($l_row['isys_obj__id']) != $p_hostname)
                {
                    continue;
                } // if

                $l_return[] = $l_row;
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * This method will return all objects, which use the given service.
     *
     * @param   string $p_service
     * @param   string $p_hostname
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_objects_by_inherited_service($p_service, $p_hostname = null)
    {
        isys_check_mk_helper::init();

        $l_return = [];
        $l_sql    = 'SELECT obj.*, isys_obj_type.* FROM isys_catg_application_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT JOIN isys_obj AS app ON app.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_obj AS obj ON obj.isys_obj__id = isys_catg_application_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = obj.isys_obj__isys_obj_type__id
			LEFT JOIN isys_catg_cmk_service_list ON isys_catg_cmk_service_list__isys_obj__id = app.isys_obj__id
			WHERE isys_catg_cmk_service_list__service LIKE ' . $this->convert_sql_text($p_service) . ';';

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                // If the given hostname does not match with the current one, it's not the right host.
                if ($p_hostname !== null && isys_monitoring_helper::render_export_hostname($l_row['isys_obj__id']) != $p_hostname)
                {
                    continue;
                } // if

                $l_return[] = $l_row;
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [
            'service'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES',
                        C__PROPERTY__INFO__DESCRIPTION => ''
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_host_service_list__service'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CMK_SERVICE__CHECK_MK_SERVICES'
                    ]
                ]
            ),
            'software_assignment' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT',
                        C__PROPERTY__INFO__DESCRIPTION => ''
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cmk_host_service_list__application__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_application_list',
                            'isys_catg_application_list__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT'
                    ]
                ]
            ),
            'description'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_host_service_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CMK_HOST_SERVICE
                    ]
                ]
            )
        ];
    } // function
} // class