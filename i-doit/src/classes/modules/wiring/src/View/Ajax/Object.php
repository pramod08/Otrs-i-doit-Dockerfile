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
namespace idoit\Module\Wiring\View\Ajax;

use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Wiring\Model\Wiring;
use idoit\View\Base;
use idoit\View\Renderable;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;

//use \isys_module_wiring as ModuleWiring;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Object extends Base implements Renderable
{
    /**
     * @var  \isys_cmdb_dao_cable_connection
     */
    private $cableDao = null;

    /**
     * @var  \isys_cmdb_dao_category_g_connector
     */
    private $connectorDao = null;

    /**
     * @param $c
     *
     * @return string
     */
    public static function smartyRecurseMultipleConnections($c)
    {
        $return = '';

        if (isset($c['multiple']) && is_array($c['multiple']))
        {
            $smarty = \isys_application::instance()->template;

            $return .= '<td>
			<table class="sub-wiring">';
            foreach ($c['multiple'] as $m)
            {
                foreach ($m as $c)
                {
                    $return .= '<tr>';

                    $smarty->assign('c', $c);
                    $return .= $smarty->fetch(dirname(dirname(dirname(__DIR__))) . '/templates/views/wiring-object-connections-recursion.tpl');

                    if (isset($c['multiple']) && is_array($c['multiple']))
                    {
                        $return .= self::smartyRecurseMultipleConnections($c);
                    }

                    $return .= '</tr>';
                }
            }

            $return .= '
				</table>
			</td>';
        }

        return $return;
    } // function

    /**
     *
     * @param   \isys_cmdb_dao_cable_connection $cableDao
     *
     * @return  $this
     */
    public function setCableDao(\isys_cmdb_dao_cable_connection $cableDao)
    {
        $this->cableDao = $cableDao;

        return $this;
    } // function

    /**
     *
     * @param   \isys_cmdb_dao_category_g_connector $connectorDao
     *
     * @return  $this
     */
    public function setConnectorDao(\isys_cmdb_dao_category_g_connector $connectorDao)
    {
        $this->connectorDao = $connectorDao;

        return $this;
    } // function

    /**
     * Process method.
     *
     * @param   ModuleBase        $p_module
     * @param   ComponentTemplate $p_template
     * @param   DaoBase|Wiring    $p_model
     *
     * @return  $this
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        if ($this->id > 0)
        {
            echo $p_template->assign('connections', $p_model->resolve($this->id))
                ->assign('types', $p_model->getConnectionTypes())
                ->assign('current', $this->id)
                ->fetch($p_module->get_template_dir() . 'views/wiring-object-connections.tpl');

            die;
        } // if

        return $this;
    }

    /**
     * Render method.
     *
     * @return  void
     */
    public function render()
    {
        // Die, because this is an ajax request and the output should already be sent
        die;
    } // function

    /**
     *
     * @param   integer $objectID
     *
     * @return  $this
     * @throws  \Exception
     */
    public function setID($objectID)
    {
        if ($objectID > 0)
        {
            $this->id = $objectID;
        }
        else
        {
            throw new \Exception(_L('LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE'));
        } // if

        return $this;
    } // function
} // class