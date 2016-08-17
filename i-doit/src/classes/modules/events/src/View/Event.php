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
namespace idoit\Module\Events\View;

use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Events\Model\Dao;
use idoit\View\Renderable;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;
use isys_module_events as ModuleEvents;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Event extends EventList implements Renderable
{

    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     * @param Dao               $p_model
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {

        /**
         * Set paths to templates
         */
        $this->paths['contentbottomcontent'] = $p_module->get_template_dir() . 'edit.tpl';

        /**
         * Activate navbar buttons
         */
        $navbar = \isys_component_template_navbar::getInstance();
        if ($this->id)
        {
            $navbar->set_active(
                \isys_auth_events::instance()
                    ->is_allowed_to(\isys_auth::EDIT, 'hooks'),
                C__NAVBAR_BUTTON__EDIT
            );
            $eventData = $p_model->getEventSubscriptions($this->id)
                ->__to_array();
        }
        else
        {
            // Defaults
            $eventData = [
                'id'      => null,
                'queued'  => 0,
                'type'    => ModuleEvents::TYPE_SHELL_COMMAND,
                'command' => '',
                'options' => '',
                'title'   => ''
            ];
        }

        // Get events
        $eventsDescriptions = $eventsArray = [];
        $events             = $p_model->getEvents();
        while ($row = $events->get_row())
        {
            $eventsArray[$row['id']]        = $row['title'];
            $eventsDescriptions[$row['id']] = _L($row['title'] . '_DESCRIPTION');
        }

        // Prepare rules
        $l_rules = [
            'event_id'   => [
                'p_strSelectedID' => $eventData['event_id'],
                'p_arData'        => serialize($eventsArray)
            ],
            'type'       => [
                'p_strSelectedID' => $eventData['type'],
                'p_arData'        => serialize(ModuleEvents::event_types())
            ],
            'mode'       => [
                'p_strSelectedID' => $eventData['queued'],
                'p_arData'        => serialize(
                    [
                        //1 => _L('LC__MODULE__EVENTS__ADD_TO_QUEUE'),
                        0 => _L('LC__MODULE__EVENTS__EXEC_LIVE')
                    ]
                )
            ],
            'title'      => [
                'p_strValue' => $eventData['title']
            ],
            'command'    => [
                'p_strValue' => $eventData['command']
            ],
            'parameters' => [
                'p_strValue' => $eventData['options']
            ]
        ];

        $p_template->assign(
            'descriptionMapping',
            \isys_format_json::encode(
                $eventsDescriptions
            )
        )
            ->assign('eventSubscriptionID', $this->id)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        return $this;
    }

}