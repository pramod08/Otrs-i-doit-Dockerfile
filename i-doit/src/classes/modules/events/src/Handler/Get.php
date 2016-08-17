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
namespace idoit\Module\Events\Handler;

use idoit\Module\Events\Handler\Output\Response;

/**
 * event handlers
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Get implements HandableEvent
{

    /**
     * @param array $event
     * @param array $args
     *
     * @return string
     */
    public function handleLive($event, $args)
    {
        return $this->send($event, $args);
    }

    /**
     * @param array $event
     * @param array $args
     *
     * @return string
     */
    public function handleQueued($event, $args)
    {
        return $this->send($event, $args);
    }

    /**
     * @param array $event
     * @param array $args
     *
     * @return string
     */
    private function send($event, $args)
    {
        //open connection
        $ch = curl_init();

        $argsString = '';
        foreach ($args as $key => $value)
        {
            $argsString .= $key . '=' . urlencode($value) . '&';
        }
        rtrim($argsString, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $event['command'] . '?' . $argsString);

        //execute post
        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);

        //close connection
        curl_close($ch);

        return new Response($result, $result ? true : false, $info[CURLINFO_HTTP_CODE]);
    }

}