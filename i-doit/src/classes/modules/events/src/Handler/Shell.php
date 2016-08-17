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
class Shell implements HandableEvent
{

    /**
     * @param array $event
     * @param array $args
     *
     * @return Response
     */
    public function handleLive($event, $args)
    {
        return $this->execute($event, $args);
    }

    /**
     * @param array $event
     * @param array $args
     *
     * @return Response
     */
    public function handleQueued($event, $args)
    {
        return $this->execute($event, $args);
    }

    /**
     * @param array $event
     * @param array $args
     *
     * @return Response
     */
    private function execute($event, $args)
    {
        if (file_exists($event['command']))
        {
            if (is_executable($event['command']))
            {
                if (\isys_tenantsettings::get('events.decodeArgs', true))
                {
                    $formattedArgs = base64_encode(\isys_format_json::encode($args));
                }
                else
                {
                    $formattedArgs = \isys_format_json::encode($args);
                }

                $l_return = [];
                exec($event['command'] . ' ' . $event['options'] . ' ' . $formattedArgs, $l_return, $l_returnCode);

                return new Response(implode("\n", $l_return), $l_returnCode, $l_returnCode == 0 ? true : false);
            }
            else throw new \Exception('Command "' . $event['command'] . '" is not executable.');
        }
        else throw new \Exception('Command "' . $event['command'] . '" does not exist.');
    }

}