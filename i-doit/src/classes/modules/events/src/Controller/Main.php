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
namespace idoit\Module\Events\Controller;

use idoit\Controller\Base;
use idoit\Controller\NavbarHandable;
use idoit\Module\Events\Model\Dao;
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
     * @var \isys_module
     */
    protected $module;

    /**
     * Default request handler, gets called in every /events request
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        // React on custom requests
        // ...
        //$p_application->template->assign('formAdditionalAction', 'action="'.$p_application->www_path.ltrim($p_request->get('REQUEST'), '/').'"');

        // Reset form_submit url since form_submit is not capable of the new url-rewrite paths..
        \isys_component_template_navbar::getInstance(
        )//->set_js_onclick('form_submit(\''.$p_application->www_path.'events\', \'POST\', \'main_content\', true);', C__NAVBAR_BUTTON__EDIT)
        //->set_js_onclick('form_submit(\''.$p_application->www_path.'events\', \'POST\', \'main_content\', true);', C__NAVBAR_BUTTON__CANCEL)
        //->set_js_onclick('form_submit(\''.$p_application->www_path.'events\', \'POST\', \'main_content\', true);', C__NAVBAR_BUTTON__PURGE)

        ->set_js_onclick(
            'document.isys_form.sort.value=\'\'; document.isys_form.navMode.value=this.getAttribute(\'data-navmode\'); $(\'isys_form\').submit();',
            C__NAVBAR_BUTTON__SAVE
        )
            ->set_js_onclick(
                'document.isys_form.sort.value=\'\'; document.isys_form.navMode.value=this.getAttribute(\'data-navmode\'); $(\'isys_form\').submit();',
                C__NAVBAR_BUTTON__EDIT
            )
            ->set_js_onclick(
                'document.isys_form.sort.value=\'\'; document.isys_form.navMode.value=this.getAttribute(\'data-navmode\'); $(\'isys_form\').submit();',
                C__NAVBAR_BUTTON__CANCEL
            )
            ->set_js_onclick(
                'document.isys_form.sort.value=\'\'; document.isys_form.navMode.value=this.getAttribute(\'data-navmode\'); $(\'isys_form\').submit();',
                C__NAVBAR_BUTTON__DELETE
            );
    }

    /**
     * @param \isys_application $p_application
     *
     * @return Dao
     */
    public function dao(\isys_application $p_application)
    {
        return Dao::instance($p_application->database);
    }

    /**
     * Build the left tree
     *
     * @param \isys_register $p_tree
     *
     * @return Node
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        // Disable sorting
        $p_tree->set_tree_sort(false);

        // Initialize tree nodes
        $l_tree = new Node('Events', '', $p_request->BASE . 'images/icons/silk/lightbulb.png');

        return $l_tree/*->add(
                Node::factory(_L('LC__CONFIGURATION'), $p_request->BASE . 'events/config', $p_request->BASE . 'images/icons/silk/cog.png')
                    ->add(Node::factory('Basis', $p_request->BASE . 'events/config', $p_request->BASE . 'images/icons/silk/page_green.png'))
                    ->add(Node::factory('Erweitert', $p_request->BASE . 'events/config', $p_request->BASE . 'images/icons/silk/page_red.png'))
            )*/
        ->add(
            new Node(
                'Hooks',
                $p_request->{'BASE'} . 'events',
                $p_request->{'BASE'} . 'images/icons/silk/layers.png',
                null,
                null,
                null,
                \isys_auth_events::instance()
                    ->is_allowed_to(\isys_auth::VIEW, 'hooks')
            )
        )
            ->add(
                new Node(
                    _L('LC__MODULE__EVENTS__HISTORY'),
                    $p_request->{'BASE'} . 'events/history',
                    $p_request->{'BASE'} . 'images/icons/silk/report.png',
                    null,
                    null,
                    null,
                    \isys_auth_events::instance()
                        ->is_allowed_to(\isys_auth::VIEW, 'history')
                )
            );
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application)
    {
        $dao = $this->dao($p_application);

        if ($p_request->get('id') > 0)
        {
            $view = $this->onEdit($p_request, $p_application);
            \isys_component_template_navbar::getInstance()
                ->set_active(false, C__NAVBAR_BUTTON__SAVE);

            return $view;
        }
        else
        {
            $view = new \idoit\Module\Events\View\EventList($p_request, $dao);
            $view->setDaoResult($dao->getEventSubscriptions());
        }

        // Return the view
        return $view;
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
    public function onCancel(\isys_register $p_request, \isys_application $p_application)
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
        // Return the view
        $view = new \idoit\Module\Events\View\Event($p_request, $this->dao($p_application));

        \isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        return $view;
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onSave(\isys_register $p_request, \isys_application $p_application)
    {
        // Check for edit right
        \isys_auth_events::instance()
            ->hooks(\isys_auth::EDIT);

        $dao  = $this->dao($p_application);
        $post = $p_request->get('POST');

        if (isset($post['eventSubscriptionID']) && $post['eventSubscriptionID'] > 0)
        {
            $dao->saveEventSubscription(
                $post['eventSubscriptionID'],
                $post['event_id'],
                $post['title'],
                $post['type'],
                $post['command'],
                $post['parameters'],
                $post['mode']
            );
        }
        else
        {
            $post['eventSubscriptionID'] = $dao->addEventSubscription(
                $post['event_id'],
                $post['title'],
                $post['type'],
                $post['command'],
                $post['parameters'],
                $post['mode']
            )
                ->get_last_insert_id();
        }

        // Return the view
        $view = new \idoit\Module\Events\View\Event($p_request, $dao);

        return $view->setID($post['eventSubscriptionID']);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onEdit(\isys_register $p_request, \isys_application $p_application)
    {
        // Return the view
        $view = new \idoit\Module\Events\View\Event($p_request, $this->dao($p_application));

        /**
         * Retrieve id from URL events/main/123 (default way)
         */
        if ($p_request->get('id'))
        {
            $view->setID($p_request->get('id'));
        }
        else
        {
            /**
             * When the user clicks on edit directly after saving, event id is only available in POST
             */
            $view->setID($_POST['eventSubscriptionID']);
        }

        \isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE);

        return $view;
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
    public function onArchive(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDelete($p_request, $p_application);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDelete(\isys_register $p_request, \isys_application $p_application)
    {
        $post = $p_request->get('POST');
        if (isset($post['id']) && is_array($post['id']))
        {
            $dao = $this->dao($p_application);
            foreach ($post['id'] as $id)
            {
                $dao->deleteSubscription($id);
            }
        }

        return $this->onDefault($p_request, $p_application);
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
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
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
    public function onUp(\isys_register $p_request, \isys_application $p_application)
    {
        return $this->onDefault($p_request, $p_application);
    }

    /**
     * @param \isys_module $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }

}