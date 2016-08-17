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
 * Events module class
 *
 * @package     modules
 * @subpackage  events
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
class isys_module_events extends isys_module implements isys_module_interface, isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = true;
    const MAIN_MENU_REWRITE_LINK = true;
    const TYPE_SHELL_COMMAND     = 1;
    const TYPE_HTTP_GET          = 2;
    const TYPE_HTTP_POST         = 3;

    // Event type constants
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * Variable which the module request class.
     *
     * @var  isys_module_request
     */
    protected $m_modreq = null;
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Return event types
     *
     * @return array
     */
    public static function event_types()
    {
        return [
            self::TYPE_SHELL_COMMAND => 'SHELL COMMAND',
            //self::TYPE_HTTP_GET      => 'HTTP GET',
            //self::TYPE_HTTP_POST     => 'HTTP POST',
        ];
    } // function

    /**
     * @param $eventRow
     * @param $args
     */
    public static function delegate($eventRow, $args)
    {
        $log = new \idoit\Module\Events\Model\Log(isys_application::instance()->database);

        try
        {
            switch ($eventRow['type'])
            {
                case self::TYPE_SHELL_COMMAND:
                    $eventHandler = new \idoit\Module\Events\Handler\Shell();
                    break;
                case self::TYPE_HTTP_GET:
                    $eventHandler = new \idoit\Module\Events\Handler\Get();
                    break;
                default:
                case self::TYPE_HTTP_POST:
                    $eventHandler = new \idoit\Module\Events\Handler\Post();
                    break;
            }

            if ($eventRow['queued'])
            {
                $response = $eventHandler->handleQueued($eventRow, $args);
            }
            else
            {
                $response = $eventHandler->handleLive($eventRow, $args);
            }

            $log->log($eventRow['id'], $eventRow['title'], $response->output, $response->success ? 1 : 0, $response->returnCode);
        }
        catch (Exception $e)
        {
            $log->log($eventRow['id'], $eventRow['title'], $e->getMessage(), 0);
        }
    }

    /**
     * Queue event
     *
     * @param $eventRow
     * @param $args
     */
    public static function queue($eventRow, $args)
    {

    }

    /**
     * Get related auth class for module
     *
     * @return isys_auth_events
     */
    public static function get_auth()
    {
        return isys_auth_events::instance();
    }

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  $this
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;

        return $this;
    } // function

    /**
     * Build breadcrumb navifation
     *
     * @param &$p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_return = [];
        //$l_gets = $this->m_modreq->get_gets();

        $l_return[] = [
            _L('LC__CONFIGURATION') => [
                C__GET__MODULE_ID => C__MODULE__EVENTS
            ]
        ];

        return $l_return;
    } // function

    /**
     * This method builds the tree for the system menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @see     isys_module_cmdb->build_tree();
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs;

        // Check if parent node is delivered
        if (null !== $p_parent && is_int($p_parent))
        {
            // Handle tree for system module
            if ($p_system_module)
            {
                $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__EVENTS;

                // Check for PRO module
                if (defined('C__MODULE__PRO'))
                {
                    global $g_config;

                    // Add node
                    $p_tree->add_node(
                        C__MODULE__EVENTS . '01338',
                        $p_parent,
                        _L('LC__MODULE__EVENTS'),
                        str_replace('index.php', '', $g_config["startpage"]) . 'events',
                        null,
                        $g_dirs["images"] . "/icons/silk/lightbulb.png"
                    );
                }
            } // if
        } // if
    } // function

} // class