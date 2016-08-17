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

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class ConnectorUpdate extends Base implements Renderable
{
    /**
     * @var  \isys_cmdb_dao_category_g_connector
     */
    private $connectorDao = null;
    /**
     * Array of connector IDs.
     *
     * @var  array
     */
    private $connectorDataIDs = [];
    /**
     * Destination connector type ID.
     *
     * @var  integer
     */
    private $connectorTypeID = 0;

    /**
     * Setter for the connector IDs.
     *
     * @param   array $dataIDs
     *
     * @return  $this
     * @throws  \Exception
     */
    public function setConnectorIDs(array $dataIDs = [])
    {
        if (count($dataIDs) > 0)
        {
            $this->connectorDataIDs = $dataIDs;
        }
        else
        {
            throw new \Exception(_L('LC__MODULE__WIRING__NO_CONNECTORS_SELECTED'));
        } // if

        return $this;
    } // function

    /**
     * Setter for the connector Typ ID.
     *
     * @param   integer $connectorTypeID
     *
     * @return  $this
     * @throws  \Exception
     */
    public function setConnectorTypeID($connectorTypeID)
    {
        if ($connectorTypeID > 0)
        {
            $this->connectorTypeID = $connectorTypeID;
        }
        else
        {
            throw new \Exception(_L('LC__MODULE__WIRING__NO_CONNECTOR_TYPE_SELECTED'));
        } // if

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
        if (count($this->connectorDataIDs) && $this->connectorTypeID > 0)
        {
            $result = [
                'success' => true,
                'data'    => null,
                'message' => null
            ];

            try
            {
                $this->connectorDao->update_connector_type($this->connectorDataIDs, $this->connectorTypeID);
            }
            catch (\Exception $e)
            {
                $result['success'] = false;
                $result['message'] = $e->getMessage();
            } // try

            \isys_core::send_header('Content-Type', 'application/json');

            echo \isys_format_json::encode($result);

            die;
        } // if

        return $this;
    } // function

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
} // class