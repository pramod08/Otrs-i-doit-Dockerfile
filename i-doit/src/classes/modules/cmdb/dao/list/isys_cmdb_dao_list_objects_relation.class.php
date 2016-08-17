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
 * List DAO: Relations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_relation extends isys_cmdb_dao_list_objects
{

    /**
     * Array with isys_weighting content
     *
     * id => title
     *
     * @var array
     */
    private $m_weightings = [];

    /**
     * This variable defines how many pages of the list component shall be loaded with one request.
     *
     * @var  integer
     */
    //protected $m_preload_pages = 10;

    /**
     * Method for retrieving additional conditions to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_conditions()
    {
        $l_return = '';
        if (isset($_GET["type"]))
        {
            $l_return = " AND (isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id($_GET["type"]) . ") ";
        } // if

        return $l_return;
    } // function

    /**
     * Method for retrieving additional joins to an object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_joins()
    {
        return '';
    } // function

    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return //'[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_relation",false,"LC__UNIVERSAL__RELATION","isys_cmdb_dao_category_g_relation::get_dynamic_properties",["isys_cmdb_dao_category_g_relation","dynamic_property_callback_relation"]],' .
            '[[' . C__PROPERTY_TYPE__STATIC . ',"title","isys_obj__title","LC__UNIVERSAL__RELATION","isys_cmdb_dao_category_g_relation::get_properties_ng",["isys_cmdb_dao_category_g_relation",false]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"relation_type","isys_relation_type__title","LC__CATG__RELATION__RELATION_TYPE","isys_cmdb_dao_category_g_relation::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_type",false,"LC__CMDB__CATG__TYPE","isys_cmdb_dao_category_g_relation::get_dynamic_properties",["isys_cmdb_dao_category_g_relation","dynamic_property_callback_type"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"weighting","isys_weighting__title","LC__CATG__RELATION__WEIGHTING","isys_cmdb_dao_category_g_relation::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_itservice",false,"LC__CMDB__CATG__IT_SERVICE","isys_cmdb_dao_category_g_relation::get_dynamic_properties",["isys_cmdb_dao_category_g_relation","dynamic_property_callback_itservice"]]]';
    } // function

    /**
     * Method for retrieving the default list query if not user defined.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_default_list_query()
    {
        /**
         * @note DS: This is a performance optimized query for ID-1858. Do not change it's sub-query behaviour.
         */
        return "SELECT isys_obj__id, isys_obj__title, isys_obj__id AS '__id__', isys_catg_relation_list__id, isys_relation_type__title, (SELECT isys_weighting__title FROM isys_weighting WHERE isys_weighting__id = isys_catg_relation_list__isys_weighting__id) AS isys_weighting__title " . "FROM isys_obj AS obj_main FORCE INDEX (default_list) " . "INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = obj_main.isys_obj__id " . "INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id " . "WHERE (isys_obj__isys_obj_type__id = " . $this->convert_sql_id(
            C__OBJTYPE__RELATION
        ) . ")" . $this->get_additional_conditions();
    } // function

    /**
     * Method for counting
     *
     * @return  integer
     */
    public function get_object_count()
    {
        /* Performance optimization*/
        $l_sql = 'SELECT COUNT(*) AS count FROM isys_catg_relation_list';

        $l_obj_status = $this->get_rec_status();
        if (!$l_obj_status) $l_obj_status = C__RECORD_STATUS__NORMAL;

        $l_sql .= ' WHERE (isys_catg_relation_list__status = ' . $l_obj_status . ')' . $this->get_additional_conditions();

        return (int) $this->retrieve($l_sql)
            ->get_row_value('count');
    } // function

    /**
     * Skip counting for relations since this is very slow for large databases
     *
     * @return array
     */
    public function get_rec_counts()
    {
        return [
            C__RECORD_STATUS__NORMAL   => null,
            C__RECORD_STATUS__TEMPLATE => null,
            C__RECORD_STATUS__ARCHIVED => null,
            C__RECORD_STATUS__DELETED  => null
        ];
    } // function

    /**
     * Relation list constructor
     */
    public function __construct($p_db)
    {
        parent::__construct($p_db);

        /* Set memory limit */
        if (($l_memlimit = isys_settings::get('system.memory-limit.relation-object-list', '1024M')))
        {
            ini_set('memory_limit', $l_memlimit);
        }
        $this->deactivate_group_by();
        // Cache weighting for a better query performance
        $l_q = $this->m_cat_dao->retrieve('SELECT isys_weighting__id AS id, isys_weighting__title AS title FROM isys_weighting');
        while ($l_r = $l_q->get_row())
        {
            $this->m_weightings[$l_r['id']] = $l_r['title'];
        }
    }
} // class