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
namespace idoit\Module\Wiring\Controller;

use idoit\Controller\Base;
use idoit\Controller\NavbarHandable;
use idoit\Module\Wiring\Model\Wiring;
use idoit\Tree\Node;
use isys_controller as Controllable;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Main extends Base implements Controllable, NavbarHandable
{
    /**
     * @var Wiring
     */
    protected $model = null;
    /**
     * @var \isys_module
     */
    protected $module;

    /**
     * @param \isys_application $p_application
     *
     * @return Wiring
     */
    public function dao(\isys_application $p_application)
    {
        if (!$this->model)
        {
            $this->model = Wiring::instance($p_application->database);
        }

        return $this->model;
    }

    /**
     * Default request handler, gets called in every /wiring request
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        // React on custom requests
        // ...

    }

    /**
     * Build the left tree.
     *
     * @param   \isys_register       $p_request
     * @param   \isys_application    $p_application
     * @param   \isys_component_tree $p_tree
     *
     * @return  Node
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        // Disable sorting.
        $p_tree->set_tree_sort(false);

        // Initialize tree nodes.
        $l_tree = new Node(_L('LC__MODULE__WIRING'), '', $p_request->BASE . 'images/icons/silk/disconnect.png');

        return $l_tree->add(
            new Node(
                _L('LC__MODULE__WIRING__OBJECT'),
                $p_request->{'BASE'} . 'wiring/object',
                $p_request->{'BASE'} . 'images/icons/silk/drive_network.png',
                null,
                null,
                null,
                \isys_auth_wiring::instance()
                    ->is_allowed_to(\isys_auth::VIEW, 'wiring_object')
            )
        );
        /*	->add(new Node(
                _L('LC__MODULE__WIRING__HOUSE'),
                $p_request->{'BASE'} . 'wiring/house',
                $p_request->{'BASE'} . 'images/icons/silk/chart_graph_blue.png',
                null,
                null,
                null,
                \isys_auth_wiring::instance()->is_allowed_to(\isys_auth::VIEW, 'wiring_house')));*/
    } // function

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onArchive(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDelete($p_request, $p_application);
    } // function

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onCancel(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     *
     * @param   \isys_register    $p_request
     * @param   \isys_application $p_application
     *
     * @return  \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application)
    {
        $dao = $this->dao($p_application);

        return new \idoit\Module\Wiring\View\Object($p_request, $dao);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDelete(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDuplicate(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onEdit(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onNew(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onPrint(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onPurge(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDelete($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onQuickPurge(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onRecycle(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     *
     * @param   \isys_register    $p_request
     * @param   \isys_application $p_application
     *
     * @return  \idoit\View\Renderable
     */
    public function onReset(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onSave(\isys_register $p_request, \isys_application $p_application)
    {
        // Check for edit right
        \isys_auth_wiring::instance()
            ->wiring(\isys_auth::EDIT);

        $dao  = $this->dao($p_application);
        $post = $p_request->get('POST');

        return -1;
    } // function

    /**
     *
     * @param   \isys_register    $p_request
     * @param   \isys_application $p_application
     *
     * @return  \idoit\View\Renderable
     */
    public function onUp(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    } // function

    /**
     * Constructor.
     *
     * @param  \isys_module $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    } // function
} // class