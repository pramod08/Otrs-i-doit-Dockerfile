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
use idoit\View\Base;
use idoit\View\Renderable;
use isys_component_list as ComponentList;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class EventList extends Base implements Renderable
{
    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        /**
         * Set paths to templates
         */
        $this->paths['contentbottomcontent'] = $p_module->get_template_dir() . 'main.tpl';

        /**
         * Auth instance
         */
        $auth = \isys_module_events::get_auth();

        \isys_component_template_navbar::getInstance()
            ->set_active(
                $auth->is_allowed_to(\isys_auth::EDIT, 'HOOKS'),
                C__NAVBAR_BUTTON__NEW
            )//->set_active($auth->is_allowed_to(\isys_auth::EDIT, 'HOOKS'), C__NAVBAR_BUTTON__EDIT)
            ->set_active($auth->is_allowed_to(\isys_auth::EDIT, 'HOOKS'), C__NAVBAR_BUTTON__DELETE);

        /**
         * Get list component
         */
        $l_list = new ComponentList();
        $l_list->set_row_modifier($this, 'rowModifier');

        if ($l_list->config(
            [
                "title"        => 'LC__MODULE__EVENTS__DESCRIPTION',
                "event_title"  => "LC__MODULE__EVENTS__EVENT",
                "type"         => "LC__MODULE__EVENTS__TYPE",
                "command"      => "LC__MODULE__EVENTS__COMMAND",
                "date_created" => "LC__UNIVERSAL__DATE_CREATED",
                "user"         => "LC__UNIVERSAL__FROM"
            ],
            \isys_core::request_url() . 'events/main/[{id}]',
            "[{id}]"
        )
        )
        {
            $p_template->assign("objectTableList", $l_list->getTempTableHtml($this->daoResult));
        }

        $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->assign('headline', _L('LC__MODULE__EVENTS__CONFIGURED_HOOKS'));

        return $this;
    }

    /**
     * @param array &$p_row
     *
     * @throws \isys_exception_locale
     */
    public function rowModifier(&$p_row)
    {
        try
        {
            $p_row['date_created'] = \isys_locale::get_instance()
                ->fmt_datetime($p_row['date_created']);

            $eventTypes           = \isys_module_events::event_types();
            $p_row['type']        = $eventTypes[$p_row['type']];
            $p_row['event_title'] = _L($p_row['event_title']);
        }
        catch (\Exception $e)
        {

        }
    }

}