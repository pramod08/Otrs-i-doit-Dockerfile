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
 * @package   i-doit
 * @author    Dennis Stücken <dstuecken@i-doit.org>
 * @version   1.0
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_cmdb_dao_relation extends isys_cmdb_dao_category_g_relation
{
    const C__DEAD_RELATION_OBJECTS          = 'objects';
    const C__DEAD_RELATION_CATEGORY_ENTRIES = 'cat_entries';

    /**
     * @var array
     */
    private $m_regenerated_tables = [];

    /**
     * Method which deletes all dead relation objects and entries
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function delete_dead_relations()
    {
        $l_dead_rel_objects = $l_delete_relation = $l_delete_dead_relations = $l_tables = [];

        $l_check_sql = 'SELECT isys_catg_relation_list__id, isys_catg_relation_list__isys_obj__id, slave.isys_obj__id AS slaveID, master.isys_obj__id AS masterID
			FROM isys_catg_relation_list
        	INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
        	LEFT JOIN isys_obj AS master ON master.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
        	LEFT JOIN isys_obj AS slave ON slave.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
          	WHERE isys_relation_type__type != ' . C__RELATION__EXPLICIT . ' AND isys_relation_type__id != ' . C__RELATION_TYPE__DATABASE_INSTANCE . ';';
        $l_res       = $this->retrieve($l_check_sql);
        // Collect all relations from table "isys_catg_relation_list" which has no slave or master
        while ($l_row = $l_res->get_row())
        {
            if (empty($l_row['masterID']) || empty($l_row['slaveID']))
            {
                $l_delete_dead_relations[$l_row['isys_catg_relation_list__id']] = $l_row['isys_catg_relation_list__isys_obj__id'];
            } // if
        } // while

        $l_check_sql = 'SELECT isys_obj__id
			FROM isys_obj
			LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_obj__id
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(C__OBJTYPE__RELATION) . '
			AND isys_catg_relation_list__id IS NULL';

        $l_res = $this->retrieve($l_check_sql);
        // Collect all relation objects which have no entry in table "isys_catg_relation_list"
        while ($l_row = $l_res->get_row())
        {
            $l_dead_rel_objects[] = $l_row['isys_obj__id'];
        } // while

        $l_amount_dead_relations = count($l_delete_dead_relations);
        // Delete relation object. If it fails add the relation id to the array which deletes only the relation entry
        if ($l_amount_dead_relations)
        {
            foreach ($l_delete_dead_relations AS $l_rel_id => $l_rel_obj_id)
            {
                $this->delete_object_and_relations($l_rel_obj_id);
                if ($this->affected_after_update() == 0)
                {
                    // Object does not exist for whatever reasons
                    // delete relation entry instead
                    if (is_numeric($l_rel_id))
                    {
                        $l_delete_relation[] = $l_rel_id;
                        $l_amount_dead_relations--;
                    } // if
                } // if
            } // foreach
        } // if

        // Delete relation objects which have no entry in isys_catg_relation_list
        if (count($l_dead_rel_objects))
        {
            foreach ($l_dead_rel_objects AS $l_dead_object)
            {
                $this->delete_object_and_relations($l_dead_object);
            } // foreach
            $l_amount_dead_relations += count($l_dead_rel_objects);
        } // if

        // Delete relation entries which have no relation object
        $l_relations_with_no_object = count($l_delete_relation);
        if ($l_relations_with_no_object)
        {
            $l_delete = 'DELETE FROM isys_catg_relation_list WHERE isys_catg_relation_list__id IN (' . implode(',', $l_delete_relation) . ')';
            $this->update($l_delete);
        } // if

        $this->apply_update();

        return [
            self::C__DEAD_RELATION_OBJECTS          => $l_amount_dead_relations,
            self::C__DEAD_RELATION_CATEGORY_ENTRIES => $l_relations_with_no_object,
        ];
    } // function

    /**
     * Regenerate relations
     *
     * @param array $p_selected_categories
     *
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function regenerate_relations($p_selected_categories = [])
    {
        $l_affected_categories     = [];
        $l_use_selected_categories = (bool) count($p_selected_categories);

        $l_all_catg = $this->get_all_catg();
        while ($l_row = $l_all_catg->get_row())
        {
            if ($l_use_selected_categories)
            {
                if (isset($p_selected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']]))
                {
                    $l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']] = $l_row;
                } // if
            }
            else
            {
                $l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']] = $l_row;
            } // if
        } // while

        $l_all_cats = $this->get_all_cats();
        while ($l_row = $l_all_cats->get_row())
        {
            if ($l_use_selected_categories)
            {
                if (isset($p_selected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']]))
                {
                    $l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']] = $l_row;
                } // if
            }
            else
            {
                $l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']] = $l_row;
            } // if
        } // while

        /* Begin transaction */
        //isys_application::instance()->database->begin();

        try
        {
            // Global
            if (isset($l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL]))
            {
                $l_sql        = 'SELECT isysgui_catg__id, isysgui_catg__class_name AS class_name, CASE WHEN LOCATE(\'_list\', isysgui_catg__source_table) = 0 OR LOCATE(\'_listener\', isysgui_catg__source_table) > 0 THEN CONCAT(isysgui_catg__source_table, \'_list\') ELSE isysgui_catg__source_table END AS source_table
					FROM isysgui_catg WHERE isysgui_catg__id IN (' . implode(',', array_keys($l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL])) . ');';
                $l_res_global = $this->retrieve($l_sql);
                while ($l_row = $l_res_global->get_row())
                {
                    if ($this->has_relation_field($l_row['source_table']))
                    {
                        $this->regenerate_category_relation($l_row['class_name'], $l_row['source_table']);
                    } // if
                } // while
            } // if
            // Specific
            if (isset($l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]))
            {
                $l_sql          = 'SELECT isysgui_cats__id, isysgui_cats__class_name AS class_name, isysgui_cats__source_table AS source_table FROM isysgui_cats WHERE isysgui_cats__id IN (' . implode(
                        ',',
                        array_keys($l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])
                    ) . ');';
                $l_res_specific = $this->retrieve($l_sql);
                while ($l_row = $l_res_specific->get_row())
                {
                    if ($this->has_relation_field($l_row['source_table']))
                    {
                        $this->regenerate_category_relation($l_row['class_name'], $l_row['source_table']);
                    } // if
                } // while
            } // if
        }
        catch (Exception $e)
        {
            //isys_application::instance()->database->rollback();
            isys_notify::error('An error occurred while regenerating relations with error message: ' . $e->getMessage());
            throw new isys_exception_general($e->getMessage());
        } // try

        /* All done, commit transaction */
        //isys_application::instance()->database->commit();
    } // function

    /**
     * Helper method which rebuilds the empty relations
     *
     * @param isys_component_dao_result $p_res
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function regenerate_category_relation($p_class_name, $p_source_table)
    {
        $l_black_list_tables = [
            'isys_catg_virtual_list' => true,
            'isys_catg_virtual'      => true
        ];
        $l_data_field        = '';

        if (!isset($l_black_list_tables[$p_source_table]) && !isset($this->m_regenerated_tables[$p_source_table]))
        {
            if (class_exists($p_class_name))
            {
                $l_dao = call_user_func(
                    [
                        $p_class_name,
                        'instance'
                    ],
                    isys_application::instance()->database
                );
                if ($l_dao->has_relation())
                {
                    $l_relation_field   = $p_source_table . '__isys_catg_relation_list__id';
                    $l_relation_handler = $l_relation_type = $l_connected_object_field = null;
                    $l_dao->unset_properties();
                    $l_properties = $l_dao->get_properties();

                    $l_object_field           = $l_dao->get_object_id_field();
                    $l_connected_object_field = $l_dao->get_connected_object_id_field();

                    foreach ($l_properties AS $l_property_key => $l_property_info)
                    {
                        if (isset($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE]))
                        {
                            //$l_connected_object_field = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                            $l_relation_type    = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE];
                            $l_relation_handler = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER];
                            $l_data_field       = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                            break;
                        } // if
                    } // foreach

                    if ($l_connected_object_field === null || $l_relation_type === null || $l_relation_handler === null)
                    {
                        return;
                    } // if

                    if (strpos($l_object_field, 'isys_connection'))
                    {
                        $l_connected_object_field = $l_object_field;
                    } // if

                    $l_sql = 'SELECT ' . $p_source_table . '__id, isys_catg_relation_list__id FROM ' . $p_source_table . ' LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = ' . $l_relation_field . ' ';
                    if (strpos($l_connected_object_field, 'isys_connection') !== false)
                    {
                        $l_connection_field = $p_source_table . '__isys_connection__id';
                        if (strpos($l_data_field, 'isys_connection') === false)
                        {
                            $l_connection_field = $l_data_field;
                        } // if

                        $l_sql .= 'INNER JOIN isys_connection ON isys_connection__id = ' . $l_connection_field . ' AND isys_connection__isys_obj__id IS NOT NULL ';
                    }
                    elseif (strpos($l_connected_object_field, '_list') !== false && strpos($l_connected_object_field, $p_source_table) === false)
                    {
                        $l_join_table           = substr($l_connected_object_field, 0, strpos($l_connected_object_field, '__'));
                        $l_join_field           = $l_join_table . '__id';
                        $l_connected_join_field = $p_source_table . '__' . $l_join_field;

                        $l_sql .= 'LEFT JOIN ' . $l_join_table . ' ON ' . $l_join_field . ' = ' . $l_connected_join_field . ' ';
                    } // if

                    //$l_sql .= 'WHERE isys_catg_relation_list__id IS NULL AND ' . $l_connected_object_field . ' IS NOT NULL;';
                    $l_sql .= 'WHERE ' . $l_connected_object_field . ' IS NOT NULL;';
                    $l_res = $l_dao->retrieve($l_sql);

                    if ($l_res->num_rows() > 0)
                    {
                        while ($l_data = $l_res->get_row())
                        {
                            $l_data_id = $l_data[$p_source_table . '__id'];
                            if (is_object($l_relation_handler))
                            {
                                $l_request = isys_request::factory()
                                    ->set_category_data_id($l_data_id);
                                if (isset($l_data[$p_source_table . '__isys_obj__id']))
                                {
                                    $l_request->set_object_id($l_data[$p_source_table . '__isys_obj__id']);
                                } // if

                                if (is_object($l_relation_type))
                                {
                                    /**
                                     * Callback method will be executed callback_property_relation_type_handler
                                     */
                                    $l_relation_type_id = $l_relation_type->execute($l_request);
                                }
                                else
                                {
                                    $l_relation_type_id = $l_relation_type;
                                } // if
                                /**
                                 * Callback method will be executed callback_property_relation_handler
                                 */
                                $l_relation_data = $l_relation_handler->execute($l_request);

                                if ($l_relation_data[C__RELATION_OBJECT__MASTER] > 0 && $l_relation_data[C__RELATION_OBJECT__SLAVE] > 0)
                                {
                                    $this->handle_relation(
                                        $l_data_id,
                                        $p_source_table,
                                        $l_relation_type_id,
                                        $l_data['isys_catg_relation_list__id'],
                                        $l_relation_data[C__RELATION_OBJECT__MASTER],
                                        $l_relation_data[C__RELATION_OBJECT__SLAVE]
                                    );

                                    if ($p_class_name === 'isys_cmdb_dao_category_g_it_service_components')
                                    {
                                        $l_rel_id = $this->retrieve(
                                            'SELECT ' . $p_source_table . '__isys_catg_relation_list__id FROM ' . $p_source_table . '
											WHERE ' . $p_source_table . '__id = ' . $this->convert_sql_id($l_data_id)
                                        )
                                            ->get_row_value($p_source_table . '__isys_catg_relation_list__id');
                                        $this->set_it_service($l_rel_id, $l_relation_data[C__RELATION_OBJECT__SLAVE]);
                                    } // if
                                } // if
                            } // if
                        } // while
                    } // if
                    $this->m_regenerated_tables[$p_source_table] = true;
                } // if
            } // if
        } // if
    } // function

    public function __construct(isys_component_database &$p_db)
    {
        parent::__construct($p_db);
    } // function
}

?>