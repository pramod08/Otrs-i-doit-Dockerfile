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

use idoit\Module\Wiring\Model\Wiring;
use isys_controller as Controllable;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Wiring
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Object extends Main implements Controllable
{

    /**
     * Default request handler, gets called in every request
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {

    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application)
    {
        try
        {
            // Check for view right first.
            \isys_auth_wiring::instance()
                ->wiring_object(\isys_auth::VIEW);

            // Check for ajax request and redirect to cable run generation.
            if (\isys_core::is_ajax_request())
            {
                return $this->onAjaxRequest($p_request, $p_application);
            } // if
        }
        catch (\Exception $e)
        {
            \isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        return new \idoit\Module\Wiring\View\Object($p_request, $this->dao($p_application));
    } // function

    /**
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onAjaxRequest(\isys_register $p_request, \isys_application $p_application)
    {
        $posts = new \isys_array($p_request->get('POST'));

        try
        {
            if ($posts->get('objID'))
            {
                /* @var  $model  Wiring */
                $model = $this->dao($p_application);
                $model->setAlignCableRun($posts['alignOutput'] ? json_decode($posts['alignOutput']) : $posts['alignOutput']);

                return (new \idoit\Module\Wiring\View\Ajax\Object($p_request, $model))->setID($posts->get('objID'))
                    ->setCableDao(new \isys_cmdb_dao_cable_connection($p_application->database))
                    ->setConnectorDao(new \isys_cmdb_dao_category_g_connector($p_application->database));
            }
            else if ($posts->get('connectors') && $posts->get('connectorType'))
            {
                // Update the given connectors.
                return (new \idoit\Module\Wiring\View\Ajax\ConnectorUpdate($p_request))->setConnectorIDs(\isys_format_json::decode($posts->get('connectors')))
                    ->setConnectorTypeID($posts->get('connectorType'))
                    ->setConnectorDao(new \isys_cmdb_dao_category_g_connector($p_application->database));
            } // if
        }
        catch (\Exception $e)
        {
            \isys_notify::error($e->getMessage());
        } // try
    } // function
} // class