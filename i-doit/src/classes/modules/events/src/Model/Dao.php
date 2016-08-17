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
namespace idoit\Module\Events\Model;

use idoit\Model\Dao\Base;

/**
 * i-doit Events Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Dao extends Base
{

    /**
     * @param null $p_id
     *
     * @return \isys_component_dao_result
     * @throws \Exception
     * @throws \isys_exception_database
     */
    public function getEvents($p_id = null, $p_order = 'isys_event__title ASC', $p_limit = null)
    {
        $select = [
            'isys_event__id'          => 'id',
            'isys_event__title'       => 'title',
            'isys_event__identifier'  => 'identifier',
            'isys_event__description' => 'description',
            'isys_event__status'      => 'status',
            'isys_module__title'      => 'mod_title',
            'isys_module__const'      => 'mod_const'
        ];

        $sql = 'SELECT ' . $this->selectImplode($select) . ' ' . 'FROM isys_event ' . 'INNER JOIN isys_module ' . 'ON isys_event__isys_module__id = isys_module__id ';

        if ($p_id)
        {
            $sql .= 'WHERE isys_event__id = ' . $this->convert_sql_id($p_id);
        }

        if ($p_order)
        {
            $sql .= 'ORDER BY ' . $p_order;
        }

        if ($p_limit)
        {
            $sql .= 'LIMIT ' . $p_limit;
        }

        return $this->retrieve($sql);
    }

    /**
     * @param null $p_id
     *
     * @return \isys_component_dao_result
     * @throws \Exception
     * @throws \isys_exception_database
     */
    public function getEventSubscriptions($p_id = null, $p_order = 'isys_event__title ASC', $p_limit = null)
    {
        $select = [
            'isys_event_subscription__id'           => 'id',
            'isys_event__id'                        => 'event_id',
            'isys_event_subscription__queued'       => 'queued',
            'isys_event_subscription__command'      => 'command',
            'isys_event_subscription__title'        => 'title',
            'isys_event_subscription__options'      => 'options',
            'isys_event_subscription__type'         => 'type',
            'isys_event_subscription__date_created' => 'date_created',
            'isys_event__title'                     => 'event_title',
            'isys_event__status'                    => 'status',
            'isys_event__identifier'                => 'identifier',
            'isys_event__handler'                   => 'handler',
            'isys_module__title'                    => 'mod_title',
            'isys_module__const'                    => 'mod_const',
            'isys_obj__title'                       => 'user'
        ];

        $sql = 'SELECT ' . $this->selectImplode(
                $select
            ) . ' ' . 'FROM isys_event_subscription ' . 'INNER JOIN isys_event ' . 'ON isys_event_subscription__isys_event__id = isys_event__id ' . 'INNER JOIN isys_module ' . 'ON isys_event__isys_module__id = isys_module__id ' . 'LEFT JOIN isys_obj ' . 'ON isys_event_subscription__isys_obj__id = isys_obj__id';

        if ($p_id)
        {
            $sql .= ' WHERE isys_event_subscription__id = ' . $this->convert_sql_id($p_id);
        }

        if ($p_order)
        {
            $sql .= ' ORDER BY ' . $p_order;
        }

        if ($p_limit)
        {
            $sql .= ' LIMIT ' . $p_limit;
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * @param $p_identifier
     *
     * @return \isys_component_dao_result
     * @throws \Exception
     * @throws \isys_exception_database
     */
    public function getEventSubscriptionsByHandler($p_handler)
    {
        $select = [
            'isys_event_subscription__id'           => 'id',
            'isys_event_subscription__queued'       => 'queued',
            'isys_event_subscription__command'      => 'command',
            'isys_event_subscription__options'      => 'options',
            'isys_event_subscription__title'        => 'title',
            'isys_event_subscription__type'         => 'type',
            'isys_event_subscription__date_created' => 'date_created',
            'isys_event__title'                     => 'event_title',
            'isys_event__status'                    => 'status',
            'isys_event__identifier'                => 'identifier',
            'isys_event__handler'                   => 'handler'
        ];

        $sql = 'SELECT ' . $this->selectImplode(
                $select
            ) . ' ' . 'FROM isys_event_subscription ' . 'INNER JOIN isys_event ' . 'ON isys_event_subscription__isys_event__id = isys_event__id ' . 'WHERE isys_event__handler = ' . $this->convert_sql_text(
                $p_handler
            );

        return $this->retrieve($sql . ';');
    }

    /**
     * @param int        $p_status
     * @param string     $p_order_by
     * @param int|string $p_limit
     *
     * @return \isys_component_dao_result
     * @throws \Exception
     * @throws \isys_exception_database
     */
    public function getEventHistory($p_status = null, $p_order_by = null, $p_limit = 500)
    {
        $select = [
            'isys_event_log__message'          => 'message',
            'isys_event_log__response'         => 'response',
            'isys_event_log__datetime'         => 'datetime',
            'isys_event_log__status'           => 'status',
            'isys_event_log__response_code'    => 'response_code',
            'isys_event_subscription__id'      => 'id',
            'isys_event_subscription__queued'  => 'queued',
            'isys_event_subscription__command' => 'command',
            'isys_event_subscription__options' => 'options',
            'isys_event_subscription__title'   => 'title',
            'isys_event_subscription__type'    => 'type',
            'isys_event__title'                => 'event_title',
            'isys_event__identifier'           => 'identifier'
        ];

        $sql = 'SELECT ' . $this->selectImplode(
                $select
            ) . ' ' . 'FROM isys_event_log ' . 'INNER JOIN isys_event_subscription ' . 'ON isys_event_log__isys_event_subscription__id = isys_event_subscription__id ' . 'INNER JOIN isys_event ' . 'ON isys_event_subscription__isys_event__id = isys_event__id ' . 'WHERE TRUE';

        if ($p_status)
        {
            $sql .= ' AND isys_event_log__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_order_by)
        {
            $sql .= ' ORDER BY ' . $p_order_by;
        }
        else
        {
            $sql .= ' ORDER BY isys_event_log__id DESC';
        }

        if ($p_limit)
        {
            $sql .= ' LIMIT ' . $p_limit;
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Add an event subscription
     *
     * @param int    $event_id
     * @param int    $type
     * @param string $command
     * @param string $options
     * @param int    $queued
     *
     * @return Dao
     * @throws \isys_exception_dao
     */
    public function addEventSubscription($eventID, $title, $type, $command, $options, $queued = 0)
    {
        $sql = 'INSERT INTO isys_event_subscription SET ' . 'isys_event_subscription__isys_event__id = ' . $this->convert_sql_id(
                $eventID
            ) . ', ' . 'isys_event_subscription__isys_obj__id = ' . $this->convert_sql_id(
                \isys_application::instance()->session->get_user_id()
            ) . ', ' . 'isys_event_subscription__date_created = NOW(), ' . 'isys_event_subscription__type = ' . $this->convert_sql_int(
                $type
            ) . ', ' . 'isys_event_subscription__command = ' . $this->convert_sql_text($command) . ', ' . 'isys_event_subscription__title = ' . $this->convert_sql_text(
                $title
            ) . ', ' . 'isys_event_subscription__options = ' . $this->convert_sql_text($options) . ', ' . 'isys_event_subscription__queued = ' . $this->convert_sql_int(
                $queued
            ) . ' ';

        $this->update($sql) && $this->apply_update();

        return $this;
    }

    /**
     * Save an event subscription
     *
     * @param int    $eventSubscriptionID
     * @param int    $event_id
     * @param int    $type
     * @param string $command
     * @param string $options
     * @param int    $queued
     *
     * @return Dao
     * @throws \isys_exception_dao
     */
    public function saveEventSubscription($eventSubscriptionID, $eventID, $title, $type, $command, $options, $queued = 0)
    {
        $sql = 'UPDATE isys_event_subscription SET ' . 'isys_event_subscription__isys_event__id = ' . $this->convert_sql_id(
                $eventID
            ) . ', ' . 'isys_event_subscription__isys_obj__id = ' . $this->convert_sql_id(
                \isys_application::instance()->session->get_user_id()
            ) . ', ' . 'isys_event_subscription__date_created = NOW(), ' . 'isys_event_subscription__type = ' . $this->convert_sql_int(
                $type
            ) . ', ' . 'isys_event_subscription__command = ' . $this->convert_sql_text($command) . ', ' . 'isys_event_subscription__options = ' . $this->convert_sql_text(
                $options
            ) . ', ' . 'isys_event_subscription__title = ' . $this->convert_sql_text($title) . ', ' . 'isys_event_subscription__queued = ' . $this->convert_sql_int(
                $queued
            ) . ' ' . 'WHERE isys_event_subscription__id = ' . $this->convert_sql_id($eventSubscriptionID);

        $this->update($sql) && $this->apply_update();

        return $this;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    public function deleteSubscription($id)
    {
        $sql = 'DELETE FROM isys_event_subscription WHERE isys_event_subscription__id = ' . $this->convert_sql_id($id) . ';';

        return $this->update($sql) && $this->apply_update();
    }

}