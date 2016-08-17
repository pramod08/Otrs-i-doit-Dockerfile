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
class Log extends Base
{

    /**
     * @param $eventSubsctiptionID
     * @param $message
     * @param $response
     * @param $status
     *
     * @return $this
     * @throws \isys_exception_dao
     */
    public function log($eventSubsctiptionID, $message, $response, $status, $responseCode = 0)
    {
        $sql = 'INSERT INTO isys_event_log SET ' . 'isys_event_log__isys_event_subscription__id = ' . $this->convert_sql_id(
                $eventSubsctiptionID
            ) . ', ' . 'isys_event_log__message = ' . $this->convert_sql_text($message) . ', ' . 'isys_event_log__response = ' . $this->convert_sql_text(
                $response
            ) . ', ' . 'isys_event_log__response_code = ' . $this->convert_sql_int(
                $responseCode
            ) . ', ' . 'isys_event_log__datetime = NOW(), ' . 'isys_event_log__status = ' . $this->convert_sql_int($status) . '';

        $this->update($sql) && $this->apply_update();

        return $this;
    }

}