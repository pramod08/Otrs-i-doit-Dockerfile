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
 * DAO: specific category for networks
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-8
 */
class isys_cmdb_dao_category_s_net_ip_addresses extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_ip_addresses';

    /**
     * Dynamic property handling for getting the net address range.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_ip_address_link(array $p_row)
    {
        $l_link = isys_application::instance()->www_path . isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT   => $p_row['isys_obj__id'],
                    C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                    C__CMDB__GET__CATS     => C__CATS__NET_IP_ADDRESSES
                ]
            );

        return '<a href="' . $l_link . '">' . $p_row['isys_obj__title'] . '</a>';
    } // function

    /**
     * Executes the query to create the category entry.
     *
     * @param   string  $p_ip_address
     * @param   integer $p_obj_id
     * @param   integer $p_type
     * @param   integer $p_status
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_ip_address, $p_obj_id = null, $p_type = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "INSERT IGNORE INTO " . $this->m_table . " SET " . "isys_cats_net_ip_addresses_list__title = " . $this->convert_sql_text(
                $p_ip_address
            ) . ", " . "isys_cats_net_ip_addresses_list__ip_address_long = '" . Ip::ip2long(
                $p_ip_address
            ) . "', " . "isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->convert_sql_id(
                $p_obj_id
            ) . ", " . "isys_cats_net_ip_addresses_list__isys_ip_assignment__id = " . $this->convert_sql_int(
                $p_type
            ) . ", " . "isys_cats_net_ip_addresses_list__status = " . $this->convert_sql_int($p_status) . ";";

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Find the IP assignment (from table "isys_ip_assignment") by a given IP-address.
     *
     * @param   string $p_ip
     *
     * @return  integer
     */
    public function get_ip_assignment_by_ip($p_ip)
    {
        // When we get an empty IP it is unnumbered.
        if ($p_ip == '0.0.0.0' || empty($p_ip))
        {
            return C__CATP__IP__ASSIGN__UNNUMBERED;
        } // if

        $l_sql = "SELECT isys_cats_net_dhcp_list__isys_net_dhcp_type__id FROM isys_cats_net_dhcp_list " . "WHERE " . Ip::ip2long(
                $p_ip
            ) . " BETWEEN isys_cats_net_dhcp_list__range_from_long AND isys_cats_net_dhcp_list__range_to_long;";

        $l_dhcp_type = $this->retrieve($l_sql)
            ->get_row_value('isys_cats_net_dhcp_list__isys_net_dhcp_type__id');

        if ($l_dhcp_type == C__NET__DHCP_RESERVED)
        {
            return C__CATP__IP__ASSIGN__DHCP_RESERVED;
        }
        else if ($l_dhcp_type == C__NET__DHCP_DYNAMIC)
        {
            return C__CATP__IP__ASSIGN__DHCP;
        } // if

        return C__CATP__IP__ASSIGN__STATIC;
    } // function

    /**
     * Save category entry.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_old_status
     * @param   boolean $p_create
     *
     * @return  int|mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_old_status, $p_create)
    {
        $l_catdata = $this->get_general_data();

        $l_list_id = $l_catdata["isys_cats_net_list__id"];

        $this->merge_posted_ip_data($_POST["C__CATS__NET__TYPE"]);

        if (empty($l_list_id)) $l_list_id = $this->create_connector("isys_cats_net_list", $l_catdata["isys_cats_net_list__isys_obj__id"]);

        $l_bRet = $this->save($l_list_id);

        $this->m_strLogbookSQL = $this->get_last_query();

        return ($l_bRet == true) ? $l_list_id : -1;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_id.
     *
     * @param   integer $p_id
     * @param   string  $p_address
     * @param   integer $p_obj_id
     * @param   integer $p_type
     * @param   integer $p_status
     *
     * @return  boolean  true, if transaction executed successfully, else false
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws isys_exception_dao
     */
    public function save($p_id, $p_address = null, $p_obj_id = null, $p_type = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = 'UPDATE ' . $this->m_table . ' SET ' . 'isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text(
                $p_address
            ) . ', ' . 'isys_cats_net_ip_addresses_list__ip_address_long = \'' . Ip::ip2long(
                $p_address
            ) . '\', ' . 'isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ', ' . 'isys_cats_net_ip_addresses_list__isys_ip_assignment__id = ' . $this->convert_sql_id(
                $p_type
            ) . ', ' . 'isys_cats_net_ip_addresses_list__status = ' . $this->convert_sql_int(
                $p_status
            ) . ' ' . 'WHERE isys_cats_net_ip_addresses_list__id = ' . $this->convert_sql_id($p_id);

        return $this->update($l_strSql) && $this->apply_update();
    } // function

    /**
     * Deletes ip from connection table.
     *
     * @param   integer $p_id
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function clear($p_id)
    {

        if (empty($p_id))
        {
            return false;
        } // if

        return $this->update(
            'DELETE FROM isys_cats_net_ip_addresses_list WHERE isys_cats_net_ip_addresses_list__id = ' . $this->convert_sql_id($p_id) . ';'
        ) && $this->apply_update();
    } // function

    /**
     * Gets last id from the category table.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_last_category_id()
    {
        return ($this->retrieve(
                'SELECT isys_cats_net_ip_addresses_list__id AS id FROM isys_cats_net_ip_addresses_list ORDER BY isys_cats_net_ip_addresses_list__id  DESC LIMIT 1'
            )
                ->get_row_value('id') + 1);
    } // function

    /**
     * Gets all assigned ips as array from the net.
     *
     * @param   integer $p_net_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_assigned_ips_as_array($p_net_obj_id)
    {
        $l_data = [];
        $l_sql  = 'SELECT isys_cats_net_ip_addresses_list__title AS ip FROM isys_cats_net_ip_addresses_list ' . 'INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' . 'WHERE isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_net_obj_id
            ) . ' ' . 'AND isys_catg_ip_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_data[] = $l_row['ip'];
            } // while
        } // if

        return $l_data;
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_ip_address_link' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__IP__URL_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Open IP list'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_ip_address_link'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM " . $this->m_table . " " . "LEFT JOIN isys_catg_ip_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " . "LEFT JOIN isys_ip_assignment ON isys_ip_assignment__id = isys_cats_net_ip_addresses_list__isys_ip_assignment__id " . "LEFT JOIN isys_obj ON isys_cats_net_ip_addresses_list__isys_obj__id = isys_obj__id " . "WHERE TRUE ";

        if ($p_id !== null)
        {
            $l_sql .= "AND isys_cats_net_ip_addresses_list__id = " . $this->convert_sql_id($p_id) . " ";
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_filter !== null)
        {
            $p_condition .= $this->prepare_filter($p_filter);
        } // if

        if (!empty($p_condition))
        {
            $l_sql .= " " . $p_condition . " ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= "AND isys_cats_net_ip_addresses_list__status = " . $this->convert_sql_int($p_status) . " ";
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_cats_net_ip_addresses_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if

        return $l_sql;
    } // function

    /**
     * Method for returning the properties.
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'net_type'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_net_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_net_type',
                            'isys_net_type__id'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => false
                    ]
                ]
            ),
            'title'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'IP Address'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_net_ip_addresses_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__LAYER2_ID'
                    ]
                ]
            ),
            'ipv4_assignment' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ASSIGN_IPV4',
                        C__PROPERTY__INFO__DESCRIPTION => 'Address allocation IPv4'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_ip_assignment__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ip_assignment',
                            'isys_ip_assignment__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATP__IP__ASSIGN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_ip_assignment',
                            'p_bDbFieldNN' => 1
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => false
                    ]
                ]
            ),
            'ipv6_assignment' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ASSIGN_IPV6',
                        C__PROPERTY__INFO__DESCRIPTION => 'Address allocation IPv6'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_ipv6_assignment__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ipv6_assignment',
                            'isys_ipv6_assignment__id'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => false
                    ]
                ]
            ),
            'object'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Object title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_ip_list__isys_obj__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__LAYER2_ID'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   int     $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        ;
    } // function
} // class