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
 * Event Manager
 *
 * Executes actions triggered by CMDB-Events like creating objects.
 * Currently only generates logbook entries for the defined events, but more
 * actions are possible.
 *
 * SINGLETON
 *
 * @package     i-doit
 * @subpackage  Events
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_event_manager
{
    /**
     * Alert level mapping.
     *
     * @var  array
     */
    private static $m_alertLevels = [
        'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED'              => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED__NOT'         => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_DELETED'               => C__LOGBOOK__ALERT_LEVEL__2,
        'C__LOGBOOK_EVENT__CATEGORY_DELETED__NOT'          => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_PURGED'                => C__LOGBOOK__ALERT_LEVEL__3,
        'C__LOGBOOK_EVENT__CATEGORY_PURGED__NOT'           => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_CHANGED'               => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT'          => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_RECYCLED'              => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__CATEGORY_RECYCLED__NOT'         => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_CREATED'                 => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__OBJECT_CREATED__NOT'            => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_CHANGED'                 => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__OBJECT_CHANGED__NOT'            => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_ARCHIVED'                => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_ARCHIVED__NOT'           => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_DELETED'                 => C__LOGBOOK__ALERT_LEVEL__2,
        'C__LOGBOOK_EVENT__OBJECT_DELETED__NOT'            => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_PURGED'                  => C__LOGBOOK__ALERT_LEVEL__3,
        'C__LOGBOOK_EVENT__OBJECT_PURGED__NOT'             => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_RECYCLED'                => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECT_RECYCLED__NOT'           => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__POBJECT_MALE_PLUG_CREATED__NOT' => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED'             => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED__NOT'        => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED'             => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED__NOT'        => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED'            => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED__NOT'       => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED'             => C__LOGBOOK__ALERT_LEVEL__2,
        'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED__NOT'        => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED'              => C__LOGBOOK__ALERT_LEVEL__3,
        'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED__NOT'         => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED'            => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED__NOT'       => C__LOGBOOK__ALERT_LEVEL__1,
        'C__LOGBOOK_ENTRY__WORKFLOW_CREATED'               => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__WORKFLOW_ACCEPTED'              => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__WORKFLOW_CANCELLED'             => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_EVENT__WORKFLOW_COMPLETED'             => C__LOGBOOK__ALERT_LEVEL__0,
        'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED'               => C__LOGBOOK__ALERT_LEVEL__1
    ];
    /**
     * Variable, holding the singleton instance.
     *
     * @static
     * @var  isys_event_manager
     */
    private static $m_instance = null;
    /**
     * isys_import__id
     *
     * @var int
     */
    private $m_import_id = null; // function

    /**
     * Method for retrieving the singleton instance.
     *
     * @static
     * @return  isys_event_manager
     */
    public static function getInstance()
    {
        if (self::$m_instance === null)
        {
            self::$m_instance = new self;
        } // if

        return self::$m_instance;
    }

    /**
     * Private clone method for providing the singleton pattern.
     */
    public function __clone()
    {
    }

    /**
     * @param $p_import_id
     */
    public function set_import_id($p_import_id)
    {
        $this->m_import_id = $p_import_id;
    }

    /**
     * Gets current import id
     *
     * @return int
     */
    public function get_import_id()
    {
        return $this->m_import_id;
    } // function

    /**
     * Trigger general event.
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_date
     * @param   integer $p_alertLevel
     * @param   string  $p_source
     * @param   integer $p_objID
     * @param   string  $p_changes
     * @param   string  $p_comment
     *
     * @return  boolean
     */
    public function triggerEvent($p_title, $p_description, $p_date = null, $p_alertLevel, $p_source, $p_objID = null, $p_changes = null, $p_comment = null)
    {
        global $g_comp_database;

        $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);

        if ($p_date == null)
        {
            $p_date = isys_glob_datetime();
        } // if

        return $l_daoLogbook->set_entry(
            $p_title,
            $p_description,
            $p_date,
            $p_alertLevel,
            $p_objID,
            null,
            null,
            null,
            $p_source,
            $p_changes,
            $p_comment
        );
    }

    /**
     * Manages an event affecting the CMDB by creating an entry in the logbook.
     *
     * @param   string  $p_strConstEvent The event constant
     * @param   string  $p_strDesc       The description of the logbook entry to create
     * @param   integer $p_nObjID        The ID of the affected object, if applicable
     * @param   integer $p_nObjTypeID    The ID of the affected object type, if applicable
     * @param   string  $p_category
     * @param   string  $p_changes
     * @param   string  $p_comment
     * @param   integer $p_reasonID
     * @param   string  $p_entry_identifier
     *
     * @return  boolean
     */
    public function triggerCMDBEvent($p_strConstEvent, $p_strDesc, $p_nObjID = null, $p_nObjTypeID = null, $p_category = null, $p_changes = null, $p_comment = null, $p_reasonID = null, $p_object_title_static = null, $p_entry_identifier = null, $p_count_changes = 0, $p_source = C__LOGBOOK_SOURCE__INTERNAL)
    {
        global $g_comp_database;

        if (!$p_object_title_static)
        {
            if ($p_nObjID)
            {
                /** @var $l_dao isys_cmdb_dao */
                $l_strObjName = isys_cmdb_dao::instance($g_comp_database)
                    ->get_obj_name_by_id_as_string($p_nObjID);
            } // if
            else
            {
                $l_strObjName = '';
            }
        }
        else $l_strObjName = $p_object_title_static;

        if ($p_nObjTypeID)
        {
            $l_strObjTypeTitle = isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_name_by_id_as_string($p_nObjTypeID);
        } // if
        else
        {
            $l_strObjTypeTitle = '';
        }

        /** @var $l_daoLogbook isys_component_dao_logbook */
        $l_daoLogbook = isys_component_dao_logbook::instance($g_comp_database);

        $l_alertlevel = (self::$m_alertLevels[$p_strConstEvent]) ? self::$m_alertLevels[$p_strConstEvent] : C__LOGBOOK__ALERT_LEVEL__0;

        // Set entry in the logbook.
        return $l_daoLogbook->set_entry(
            $p_strConstEvent,
            $p_strDesc,
            isys_glob_datetime(),
            $l_alertlevel,
            $p_nObjID,
            $l_strObjName,
            $l_strObjTypeTitle,
            $p_category,
            $p_source,
            $p_changes,
            $p_comment,
            $p_reasonID,
            $p_entry_identifier,
            $p_count_changes
        );
    } // function

    /**
     * Manages an event affecting the Workflows by creating an entry in the logbook.
     *
     * @param         $p_strConstEvent  The event constant
     * @param         $p_strDesc        The description of the logbook entry to create
     * @param         $p_wID            The ID of the affected workflow, if applicable
     * @param         $p_wTypeID        The type ID of the affected workflow, if applicable
     * @param         $p_title          The title of the event
     * @param   null  $p_changes
     * @param   null  $p_comment
     *
     * @return  boolean
     */
    public function triggerWorkflowEvent($p_strConstEvent, $p_strDesc, $p_wID = null, $p_wTypeID = null, $p_title = null, $p_changes = null, $p_comment = null)
    {
        global $g_comp_database;

        $l_workflow_dao      = new isys_workflow_dao($g_comp_database);
        $l_workflow_type_dao = new isys_workflow_dao_type($g_comp_database);
        $l_wTitle            = $l_workflow_dao->get_title_by_id($p_wID);
        $l_workflow_type__id = $l_workflow_dao->get_workflow_type_by_id($p_wID);
        $l_wTypeTitle        = $l_workflow_type_dao->get_title_by_id($l_workflow_type__id);

        $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);

        // Set entry in the logbook.
        return $l_daoLogbook->set_entry(
            $p_strConstEvent,
            $p_strDesc,
            isys_glob_datetime(),
            self::$m_alertLevels[$p_strConstEvent],
            null,
            $l_wTitle,
            $l_wTypeTitle,
            null,
            ISYS_DAO_CMPNT_LOGBOOK_DEFAULT_SOURCE_ID,
            $p_changes,
            $p_comment
        );
    } // function

    /**
     * Manages an event affecting the import by creating an entry in the logbook
     *
     * @param      $p_strConstEvent
     * @param      $p_strDesc
     * @param      $p_import_id
     * @param null $p_nObjID
     * @param null $p_nObjTypeID
     * @param null $p_category
     * @param null $p_changes
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function triggerImportEvent($p_strConstEvent, $p_strDesc, $p_nObjID = null, $p_nObjTypeID = null, $p_category = null, $p_changes = null, $p_comment = null, $p_reasonID = null, $p_object_title_static = null, $p_import_id = null, $p_count_changes = 0, $p_source = C__LOGBOOK_SOURCE__IMPORT)
    {
        if ($this->triggerCMDBEvent(
            $p_strConstEvent,
            $p_strDesc,
            $p_nObjID,
            $p_nObjTypeID,
            $p_category,
            $p_changes,
            $p_comment,
            $p_reasonID,
            $p_object_title_static,
            null,
            $p_count_changes,
            $p_source
        )
        )
        {
            if (!$p_import_id)
            {
                $p_import_id = $this->m_import_id;
            }

            if ($p_import_id)
            {
                global $g_comp_database;
                isys_component_dao_logbook::instance($g_comp_database)
                    ->set_import_entry($p_import_id);
            }
        }

    } // function

    /**
     * Method for translating the current event.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     *
     * @param   string                                   $p_strEvent
     * @param   string                                   $p_name
     * @param   string                                   $p_category
     * @param   string                                   $p_objType
     * @param   string                                   $p_entry_identifier
     * @param    int                                     $p_changed_entries
     *
     * @return  string
     */
    public function translateEvent($p_strEvent, $p_name, $p_category, $p_objType, $p_entry_identifier = null, $p_changed_entries = 0)
    {
        global $g_comp_template_language_manager;

        $l_entry_lc = 'LC__LOGBOOK__CATEGORY_ENTRY';
        if ($p_changed_entries > 1)
        {
            $l_entry_lc = sprintf(_L('LC__LOGBOOK__CATEGORY_ENTRIES'), $p_changed_entries);
        }
        if (isset($p_entry_identifier) && !empty($p_entry_identifier))
        {
            $l_entry_lc = 'LC__LOGBOOK__SPECIFIC_CATEGORY_ENTRY';
        } // if

        switch ($p_strEvent)
        {
            case 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': "' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' "' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_DELETED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': "' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' "' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_DELETED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_PURGED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': "' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' "' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_CHANGED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' ' . '"' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__CATEGORY_UPDATED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' ' . '"' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__CATEGORY_UPDATED__NOT');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': "' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' "' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_RECYCLED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CREATED':
                return $g_comp_template_language_manager->get('LC__CMDB__CATG__ODEP_OBJ') . ' (' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__TYPE'
                ) . ': ' . '"' . $g_comp_template_language_manager->get($p_objType) . '") ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_CREATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CHANGED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '") ' . ((strlen($p_category) > 0) ? (':' . $g_comp_template_language_manager->get(
                        'LC__CMDB__CATG__CATEGORY'
                    ) . ' ' . '"' . $g_comp_template_language_manager->get($p_category) . '" ') : '') . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__CATEGORY_UPDATED'
                );
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CHANGED__NOT':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' . $g_comp_template_language_manager->get(
                    $p_category
                ) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__CATEGORY_UPDATED__NOT');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_DELETED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_DELETED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_PURGED':
                return $p_category . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY'
                );
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_RECYCLED':
                return $p_name . ' (' . $g_comp_template_language_manager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . '"): ' . $g_comp_template_language_manager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_RECYCLED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_RECYCLED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__POBJECT_MALE_PLUG_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED':
                return $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE') . ' ' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_CREATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED':
                return $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE') . ' ' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__CATEGORY_UPDATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED':
                return $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE') . ' ' . $g_comp_template_language_manager->get(
                    $p_objType
                ) . ' ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED':
                return $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE') . ' ' . $p_category . ' ' . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY'
                );
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED__NOT':
                break;

            case 'C__LOGBOOK_ENTRY__WORKFLOW_CREATED':
                return $g_comp_template_language_manager->get($p_objType) . ' "' . $p_name . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__OBJECT_CREATED');
                break;
            case 'C__LOGBOOK_EVENT__WORKFLOW_ACCEPTED':
                return $g_comp_template_language_manager->get($p_objType) . ' "' . $p_name . '" ' . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__WORKFLOW_HAS_BEEN_ACCEPTED'
                );
                break;
            case 'C__LOGBOOK_EVENT__WORKFLOW_CANCELLED':
                return $g_comp_template_language_manager->get($p_objType) . ' "' . $p_name . '" ' . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__WORKFLOW_HAS_BEEN_CANCELLED'
                );
                break;
            case 'C__LOGBOOK_EVENT__WORKFLOW_COMPLETED':
                return $g_comp_template_language_manager->get($p_objType) . ' "' . $p_name . '" ' . $g_comp_template_language_manager->get(
                    'LC__LOGBOOK__WORKFLOW_HAS_BEEN_CONDUCTED'
                );
                break;
            case 'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED':
                return _L('LC__LOGBOOK__TEMPLATE_HAS_BEEN_APPLIED', $p_category);
                break;

            case 'C__LOGBOOK_ENTRY__MASS_CHANGE_APPLIED':
                return $g_comp_template_language_manager->get('LC__LOGBOOK__MASS_CHANGES_FOR_OBJECT') . ' ' . $p_name . ' (' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__TYPE'
                ) . ': ' . '"' . $g_comp_template_language_manager->get($p_objType) . '"): ' . 'In ' . $g_comp_template_language_manager->get(
                    'LC__CMDB__CATG__CATEGORY'
                ) . ' ' . '"' . $g_comp_template_language_manager->get($p_category) . '" ' . $g_comp_template_language_manager->get('LC__LOGBOOK__HAS_BEEN_APPLIED');
                break;

            default:
                return $p_strEvent;
                break;
        } // switch
    } // function

    /**
     * Private constructor for providing the singleton pattern.
     */
    private function __construct()
    {
    } // function
} // class