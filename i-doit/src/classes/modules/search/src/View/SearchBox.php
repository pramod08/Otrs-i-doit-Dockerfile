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
namespace idoit\Module\Search\View;

use idoit\Model\Dao\Base as DaoBase;
use idoit\View\Base;
use idoit\View\Renderable;
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
class SearchBox extends Base implements Renderable
{
    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        try
        {
            $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->assign('headline', _L('LC__MODULE__SEARCH__TITLE'));

            /**
             * Check access rights
             */
            \isys_auth_search::instance()
                ->check(\isys_auth::EMPTY_ID_PARAM, "search");

            /**
             * Set paths to templates
             */
            $this->paths['contentbottomcontent'] = $p_module->get_template_dir() . 'index.tpl';
            $this->paths['contenttop']           = false;

        }
        catch (\isys_exception_auth $e)
        {
            $p_template->assign("exception", $e->write_log());
            $p_template->include_template('contentbottomcontent', 'exception-auth.tpl');
        }

        return $this;
    }

}