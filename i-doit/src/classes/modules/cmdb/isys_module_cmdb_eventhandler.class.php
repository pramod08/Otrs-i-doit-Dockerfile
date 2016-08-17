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
 * CMDB module eventhandler
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_cmdb_eventhandler
{

    /**
     * Triggers an event
     *
     * @param $eventHandler
     * @param $args
     */
    public static function trigger($eventHandler, $args)
    {
        $events = \idoit\Module\Events\Model\Dao::instance(isys_application::instance()->database)
            ->getEventSubscriptionsByHandler($eventHandler);

        while ($row = $events->get_row())
        {
            isys_module_events::delegate($row, $args);
        }
    }

    /**
     * Returns all corresponding signals as hookable events
     *
     * @used in global hook method isys_module_cmdb::hooks
     *
     * @return isys_array
     */
    public static function hooks()
    {
        return new isys_array(
            [
                'mod.cmdb.objectCreated'            => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__OBJECT_CREATED',
                    'handler' => 'isys_module_cmdb_eventhandler::onObjectCreated'
                ],
                'mod.cmdb.objectDeleted'            => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__OBJECT_DELETED',
                    'handler' => 'isys_module_cmdb_eventhandler::onObjectDeleted'
                ],
                'mod.cmdb.afterCreateCategoryEntry' => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_CATEGORY_CREATE',
                    'handler' => 'isys_module_cmdb_eventhandler::onAfterCategoryEntryCreate'
                ],
                'mod.cmdb.afterCategoryEntrySave'   => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_CATEGORY_SAVE',
                    'handler' => 'isys_module_cmdb_eventhandler::onAfterCategoryEntrySave'
                ],
                'mod.cmdb.beforeRankRecord'         => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__BEFORE_RANK',
                    'handler' => 'isys_module_cmdb_eventhandler::onBeforeRankRecord'
                ],
                'mod.cmdb.afterObjectTypeSave'      => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_OBJECT_TYPE_SAVE',
                    'handler' => 'isys_module_cmdb_eventhandler::onAfterObjectTypeSave'
                ],
                'mod.cmdb.afterObjectTypePurge'     => [
                    'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_OBJECT_TYPE_PURGE',
                    'handler' => 'isys_module_cmdb_eventhandler::onAfterObjectTypePurge'
                ]
            ]
        );
    }

    /**
     * @param int    $p_objectID
     * @param int    $p_sysID
     * @param int    $p_objectTypeID
     * @param string $p_objectTitle
     * @param int    $p_cmdbStatus
     * @param string $p_username
     */
    public function onObjectCreated($p_objectID, $p_sysID, $p_objectTypeID, $p_objectTitle, $p_cmdbStatus, $p_username)
    {
        $l_dao        = isys_cmdb_dao::factory(isys_application::instance()->database);
        $l_objectType = $l_dao->get_object_type($p_objectTypeID);

        self::trigger(
            __METHOD__,
            [
                'id'              => $p_objectID,
                'title'           => $p_objectTitle,
                'cmdbStatusID'    => $p_cmdbStatus,
                'cmdbStatus'      => _L(
                    $l_dao->get_object($p_objectID)
                        ->get_row_value('isys_cmdb_status__title')
                ),
                'objectTypeID'    => $p_objectTypeID,
                'objectTypeConst' => $l_objectType['isys_obj_type__const'],
                'objectType'      => _L($l_objectType['isys_obj_type__title']),
                'sysID'           => $p_sysID,
                'username'        => $p_username
            ]
        );
    }

    /**
     * @param int $p_objectID
     */
    public function onObjectDeleted($p_objectID)
    {
        self::trigger(
            __METHOD__,
            [
                'id'    => $p_objectID,
                'title' => isys_cmdb_dao::factory(isys_application::instance()->database)
                    ->get_obj_name_by_id_as_string($p_objectID),
                'type'  => _L(
                    isys_cmdb_dao::factory(isys_application::instance()->database)
                        ->get_obj_type_name_by_obj_id($p_objectID)
                )
            ]
        );
    }

    /**
     * @param isys_cmdb_dao $p_dao
     * @param int           $p_object_id
     * @param int           $p_category_id
     * @param string        $p_title
     * @param array         $p_row
     * @param string        $p_table
     * @param int           $p_currentStatus
     * @param int           $p_newStatus
     * @param int           $p_categoryType
     * @param int           $p_direction
     */
    public function onBeforeRankRecord(isys_cmdb_dao $p_dao, $p_object_id, $p_category_id, $p_title, $p_row, $p_table, $p_currentStatus, $p_newStatus, $p_categoryType, $p_direction)
    {
        $l_data         = [];
        $l_source_table = 'isys_obj';

        if (!is_null($p_category_id) && $p_category_id > 0)
        {
            $l_data = [];

            if (is_a($p_dao, 'isys_cmdb_dao_category'))
            {
                if (!$p_row || !is_array($p_row))
                {
                    /**
                     * @var isys_cmdb_dao_category $p_dao
                     */
                    $p_row = $p_dao->get_data_by_id($p_category_id)
                        ->get_row();
                }

                $l_source_table = $p_dao->get_source_table();
            }

        }

        if ($p_dao instanceof isys_cmdb_dao_category_g_custom_fields)
        {
            if (is_array($p_row))
            {
                foreach ($p_row as $key => $row)
                {
                    if (isset($row['isys_catg_custom_fields_list__field_key']) && isset($row['isys_catg_custom_fields_list__field_content']))
                    {
                        if (is_scalar($row['isys_catg_custom_fields_list__field_key']))
                        {
                            $l_data[$row['isys_catg_custom_fields_list__field_key']] = _L($row['isys_catg_custom_fields_list__field_content']);
                        }
                    }
                }
            }
        }
        else
        {
            if (is_array($p_row))
            {
                foreach ($p_row as $l_key => $l_value)
                {
                    if ($l_key == 'isys_obj__title')
                    {
                        $l_new_key = 'object';
                    }
                    else if ($l_key == 'isys_obj_type__title')
                    {
                        $l_new_key = 'objectType';
                        $l_value   = _L($l_value);
                    }
                    else if ($l_key == 'isys_obj_type__const')
                    {
                        $l_new_key = 'objectTypeConst';
                    }
                    else
                    {
                        $l_new_key = str_replace('_', '', str_replace('_list', '', str_replace($l_source_table, '', $l_key)));
                    }

                    if (!strstr($l_new_key, '__') && !strstr($l_new_key, 'isys'))
                    {
                        $l_data[$l_new_key] = $l_value;
                    }
                }
            }
        }

        self::trigger(
            __METHOD__,
            [
                'title'          => $p_title,
                'objID'          => $p_object_id,
                'categoryID'     => method_exists($p_dao, 'get_category_id') ? $p_dao->get_category_id() : null,
                'categoryDataID' => $p_category_id,
                'categoryConst'  => method_exists($p_dao, 'get_category_const') ? $p_dao->get_category_const() : null,
                'currentStatus'  => $p_currentStatus,
                'newStatus'      => $p_newStatus,
                'data'           => $l_data,
                'direction'      => $p_direction
            ]
        );
    }

    /**
     * @param isys_cmdb_dao_category $p_dao
     * @param int                    $p_category_id
     * @param int                    $p_object_id
     * @param array                  $p_posts
     * @param array                  $p_changes
     */
    public function onAfterCategoryEntrySave(isys_cmdb_dao_category $p_dao, $p_category_id, $l_saveSuccess, $p_object_id, $p_posts, $p_changes)
    {
        if (!$p_category_id && !$p_dao->is_multivalued())
        {
            $l_source_table = strstr($p_dao->get_source_table(), '_list') ? $p_dao->get_source_table() : $p_dao->get_source_table() . '_list';
            $p_category_id  = $p_dao->get_data_by_object($p_object_id)
                ->get_row_value($l_source_table . '__id');
        }

        self::trigger(
            __METHOD__,
            [
                'success'        => is_null($l_saveSuccess) ? 1 : 0,
                'objectID'       => $p_object_id,
                'categoryID'     => $p_dao->get_category_id(),
                'categoryConst'  => method_exists($p_dao, 'get_category_const') ? $p_dao->get_category_const() : false,
                'categoryDataID' => $p_category_id,
                'multivalue'     => $p_dao->is_multivalued(),
                'changes'        => $p_changes,
                'postData'       => $p_posts,
                'data'           => isys_cmdb_dao_category_data::initialize($p_object_id)
                    ->path(method_exists($p_dao, 'get_category_const') ? $p_dao->get_category_const() : 'C__CATG__GLOBAL')
                    ->data()
                    ->toArray()
            ]
        );
    }

    /**
     * @param $p_type_id
     * @param $p_posts
     * @param $p_return
     */
    public function onAfterObjectTypeSave($p_type_id, $p_posts, $p_success)
    {
        $l_dao         = new isys_cmdb_dao(isys_application::instance()->database);
        $l_objtypeData = $l_dao->get_objtype($p_type_id)
            ->get_row();

        self::trigger(
            __METHOD__,
            [
                'success'      => $p_success,
                'typeID'       => $p_type_id,
                'postData'     => $p_posts,
                'title'        => _L($l_objtypeData['isys_obj_type__title']),
                'description'  => $l_objtypeData['isys_obj_type__description'],
                'const'        => $l_objtypeData['isys_obj_type__const'],
                'status'       => $l_objtypeData['isys_obj_type__status'],
                'visible'      => $l_objtypeData['isys_obj_type__show_in_tree'],
                'locationType' => $l_objtypeData['isys_obj_type__show_in_rack'],
                'color'        => $l_objtypeData['isys_obj_type__color'],
                'sysidPrefix'  => $l_objtypeData['isys_obj_type__sysid_prefix']
            ]
        );
    }

    /**
     * @param $p_type_id
     * @param $p_title
     * @param $p_success
     */
    public function onAfterObjectTypePurge($p_type_id, $p_title, $p_success, $p_data)
    {
        self::trigger(
            __METHOD__,
            [
                'success'      => $p_success,
                'typeID'       => $p_type_id,
                'title'        => _L($p_title),
                'description'  => $p_data['isys_obj_type__description'],
                'const'        => $p_data['isys_obj_type__const'],
                'status'       => $p_data['isys_obj_type__status'],
                'visible'      => $p_data['isys_obj_type__show_in_tree'],
                'locationType' => $p_data['isys_obj_type__show_in_rack'],
                'color'        => $p_data['isys_obj_type__color'],
                'sysidPrefix'  => $p_data['isys_obj_type__sysid_prefix']
            ]
        );
    }

    /**
     * @param int   $p_categoryID
     * @param int   $p_categoryEntryID
     * @param bool  $p_result
     * @param array $p_changes
     */
    public function onAfterCategoryEntryCreate($p_categoryID, $p_categoryEntryID, $p_result, $p_object_id, isys_cmdb_dao_category $p_dao, $p_changes)
    {
        self::trigger(
            __METHOD__,
            [
                'success'        => is_null($p_result) ? 1 : 0,
                'objectID'       => $p_object_id,
                'categoryID'     => $p_dao->get_category_id(),
                'categoryDataID' => $p_categoryEntryID,
                'categoryConst'  => method_exists($p_dao, 'get_category_const') ? $p_dao->get_category_const() : false,
                'multivalue'     => $p_dao->is_multivalued()
            ]
        );
    }
}