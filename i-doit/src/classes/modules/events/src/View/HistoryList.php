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
class HistoryList extends Base implements Renderable
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

        \isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__NEW)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE);

        /**
         * Get list component
         */
        $l_list = new ComponentList();
        $l_list->set_row_modifier($this, 'rowModifier');

        if ($l_list->config(
            [
                "datetime"    => "LC_UNIVERSAL__DATE",
                "message"     => 'LC__UNIVERSAL__MESSAGE',
                "response"    => 'LC__MODULE__EVENTS__RESPONSE',
                "status"      => "LC__MODULE__EVENTS__STATUS_CODE",
                "title"       => 'LC__MODULE__EVENTS__DESCRIPTION',
                "event_title" => "LC__MODULE__EVENTS__EVENT",
                "command"     => "LC__MODULE__EVENTS__COMMAND",
                "type"        => "LC__MODULE__EVENTS__TYPE"
            ]
        )
        )
        {
            $p_template->assign("objectTableList", $l_list->getTempTableHtml($this->daoResult));
        }

        $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->assign('headline', _L('LC__MODULE__EVENTS__HISTORY') . ' (' . _L('LC__MODULE__EVENTS__HISTORY_LATEST', 500) . ')');

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
            $p_row['datetime'] = \isys_locale::get_instance()
                ->fmt_datetime($p_row['datetime']);

            switch ($p_row['status'])
            {
                case 0:
                    $p_row['status'] = '<img src="' . \isys_application::instance()->www_path . 'images/icons/silk/bullet_red.png" /> ' . _L(
                            'LC__MODULE__EVENTS__ERROR'
                        ) . ' (' . $p_row['response_code'] . ')';
                    break;
                default:
                case 1:
                    $p_row['status'] = '<img src="' . \isys_application::instance()->www_path . 'images/icons/silk/bullet_green.png" /> ' . _L(
                            'LC__MODULE__EVENTS__OK'
                        ) . ' (' . $p_row['response_code'] . ')';
                    break;
            }

            $eventTypes           = \isys_module_events::event_types();
            $p_row['type']        = $eventTypes[$p_row['type']];
            $p_row['event_title'] = _L($p_row['event_title']);

            if (strlen($p_row['response']) > 50)
            {
                $p_row['response'] = '<span>' . isys_glob_str_stop(
                        $p_row['response'],
                        50
                    ) . '</span> <a href="javascript:;" onclick="this.next().show();this.hide();">&raquo;</a>' . '<pre style="display:none;margin-top:10px;" class="code">' . strip_tags(
                        $p_row['response']
                    ) . '</pre>';
            }

        }
        catch (\Exception $e)
        {

        }
    }

}

