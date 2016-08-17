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
namespace idoit\Module\Wiring\Model;

use idoit\Model\Dao\Base;

/**
 * i-doit Events Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Wiring extends Base
{
    /**
     * Defines wheather to align the selected object under each other or not.
     *
     * @var  boolean
     */
    private $alignCableRun = true;
    /**
     * @var  \isys_cmdb_dao_cable_connection
     */
    private $cableConnectionDao;
    /**
     * @var  \isys_cmdb_dao_category_g_connector
     */
    private $connectorDao;
    /**
     * Internal location patch cache.
     *
     * @var  array
     */
    private $m_location_path = [];
    /**
     * Counter of maximum hops in last cable run.
     *
     * @var  integer
     */
    private $maxHops = 0;
    /**
     * Counter of maximum left hops in last cable run.
     *
     * @var  integer
     */
    private $maxLeftHops = 0;
    /**
     * Defines wheather to show empty run.
     *
     * @var  boolean
     */
    private $showEmptyRuns = false;

    /**
     *
     * @param   boolean $active
     *
     * @return  $this
     */
    public function setAlignCableRun($active = true)
    {
        $this->alignCableRun = $active;

        return $this;
    } // function

    /**
     * Return maximum hops counter of last ->resolve().
     *
     * @return  integer
     */
    public function getMaxHops()
    {
        return $this->maxHops;
    } // function

    /**
     * Return maximum left hops counter of last ->resolve().
     *
     * @return  integer
     */
    public function getMaxLeftHops()
    {
        return $this->maxLeftHops;
    }

    /**
     * @return  \isys_cmdb_dao_cable_connection.
     */
    public function cableConnectionDao()
    {
        return $this->cableConnectionDao;
    } // function

    /**
     * @return  array
     */
    public function getConnectionTypes()
    {
        $return   = [];
        $conTypes = $this->cableConnectionDao->get_connection_types();

        while ($row = $conTypes->get_row())
        {
            $return[$row['isys_connection_type__id']] = [
                'title' => _L($row['isys_connection_type__title']),
                'color' => $this->createColor($row['isys_connection_type__const'])
            ];
        } // while

        return $return;
    } // function

    /**
     * Resolve cable run
     *
     * @param $p_object_id
     *
     * @return array
     */
    public function resolve($p_object_id)
    {
        \isys_application::instance()->session->write_close();

        // Initialize return array
        $return = [];

        if ($p_object_id > 0 && defined('C__CONNECTOR__OUTPUT'))
        {
            // Get all Connectors.
            $l_catdata = $this->connectorDao->get_data(
                null,
                $p_object_id,
                ' AND (isys_catg_connector_list.isys_catg_connector_list__type = ' . C__CONNECTOR__OUTPUT . ' OR ISNULL(isys_catg_connector_list.isys_catg_connector_list__isys_catg_connector_list__id))',
                null,
                C__RECORD_STATUS__NORMAL,
                "isys_catg_connector_list__type DESC, isys_catg_connector_list__title",
                ''
            );

            if ($l_catdata->num_rows() > 0)
            {
                // Resolve cable run for outputs.
                while ($l_row = $l_catdata->get_row())
                {
                    $cableRun = [];

                    $cableRun[] = $this->formatConnector(
                        $l_row,
                        []
                    );

                    /**
                     * Prepare directions depending on the connector type
                     */
                    if ($l_row['isys_catg_connector_list__type'] == C__CONNECTOR__INPUT)
                    {
                        $leftID  = $l_row['isys_catg_connector_list__id'];
                        $rightID = $l_row['isys_catg_connector_list__isys_catg_connector_list__id'];
                    }
                    else
                    {
                        $leftID  = $l_row['isys_catg_connector_list__isys_catg_connector_list__id'];
                        $rightID = $l_row['isys_catg_connector_list__id'];
                    }

                    //
                    // Recurse left direction
                    //
                    $this->recurseCableRun($leftID, $cableRun);

                    // Calculate hops for left run
                    $cnt = count($cableRun) - 1;

                    // Update max hops
                    if ($this->maxLeftHops < $cnt)
                    {
                        $this->maxLeftHops = $cnt;
                    } // if

                    if ($cnt > 0)
                    {
                        // Reverse array to have everything in the right place and start the chain with the entrypoint.
                        $cableRun = array_reverse($cableRun);
                    } // if

                    //
                    // Recurse right direction
                    //
                    $this->recurseCableRun($rightID, $cableRun);

                    // Swap port and sibling for first element.
                    if (count($cableRun) > 1 && isset($cableRun[0]))
                    {
                        $cableRun[0]['siblingType'] = $cableRun[0]['portType'];
                        $cableRun[0]['siblingID']   = $cableRun[0]['portID'];
                        $cableRun[0]['sibling']     = $cableRun[0]['port'];
                        $cableRun[0]['port']        = null;
                    } // if

                    $hops = count($cableRun);
                    if ($this->maxHops < $hops) $this->maxHops = $hops;

                    // Only attach cable run if net hops are greater than zero
                    if ($hops > 1 || $this->showEmptyRuns)
                    {
                        $return[] = $cableRun;
                    } // if
                } // while
            } // if
        } // if

        if ($this->alignCableRun && $this->maxLeftHops > 0)
        {
            //$this->maxLeftHops++;
            foreach ($return as $i => $run)
            {
                $leftHops = 0;
                foreach ($run as $innerrun)
                {
                    if ($innerrun['objID'] == $p_object_id) break;

                    $leftHops++;
                }

                $tmp = array_reverse($run);
                // Fill missing hops to line the array up
                while ($leftHops < $this->maxLeftHops)
                {
                    $tmp[] = $this->formatDummy();
                    $leftHops++;
                } // while

                $return[$i] = array_reverse($tmp);
            }

        }

        return $return;
    } // function

    /**
     * Create pastel color by name.
     *
     * @param   string $name
     *
     * @return  string
     */
    private function createColor($name)
    {
        $hash = md5($name);
        // The initial substr positioning has been used to retrieve a nice "blue" for the string "C__CONNECTION_TYPE__RJ45".
        $color1 = hexdec(substr($hash, 6, 2));
        $color2 = hexdec(substr($hash, 0, 2));
        $color3 = hexdec(substr($hash, 4, 2));

        if ($color1 < 128)
        {
            $color1 += 128;
        } // if

        if ($color2 < 128)
        {
            $color2 += 128;
        } // if

        if ($color3 < 128)
        {
            $color3 += 128;
        } // if

        return "#" . dechex($color1) . dechex($color2) . dechex($color3);
    } // function

    /**
     *
     * @param   array $row
     * @param   array $sibling
     *
     * @return  array
     */
    private function formatConnector($row, $sibling)
    {
        if (!isset($this->m_location_path[$row["isys_obj__id"]]))
        {
            $this->m_location_path[$row["isys_obj__id"]] = \isys_factory::get_instance('isys_popup_browser_location')
                ->set_format_as_text(true)
                ->format_selection($row["isys_obj__id"]);
        } // if

        $return = [
            "objID"        => $row["isys_obj__id"],
            "object"       => $row["isys_obj__title"],
            "objectType"   => _L($row["object_type"]),
            "portID"       => $row["isys_catg_connector_list__id"],
            "port"         => $row["isys_catg_connector_list__title"],
            "portType"     => $row["isys_connection_type__id"] ?: $row['isys_catg_connector_list__isys_connection_type__id'],
            "siblingID"    => $sibling["isys_catg_connector_list__id"],
            "sibling"      => $sibling["isys_catg_connector_list__title"],
            "siblingType"  => $sibling["isys_connection_type__id"] ?: $sibling['isys_catg_connector_list__isys_connection_type__id'],
            "type"         => $row["isys_connection_type__title"],
            "cable"        => $row['cable_title'] ?: $this->cableConnectionDao->get_obj_name_by_id_as_string($row["isys_cable_connection__isys_obj__id"]),
            //"connectorType" => $row["isys_catg_connector_list__type"],
            "locationPath" => $this->m_location_path[$row["isys_obj__id"]]
        ];

        // Add some specific data for our JS search.
        $return['search'] = strtolower($return['objectType'] . ' ' . $return['object'] . ' ' . $return['port'] . ' ' . $return['cable'] . ' ' . $return['sibling']);

        return $return;
    } // function

    /**
     * @return  array
     */
    private function formatDummy()
    {
        return [
            "object"      => ' ',
            "objectType"  => '',
            "objID"       => 0,
            "port"        => '',
            "portType"    => '',
            "sibling"     => false,
            "siblingType" => 0,
            "type"        => '',
            "cable"       => ''
        ];
    } // function

    /**
     *
     * @param   int   $connectorID
     * @param   array &$cableRun
     *
     * @return array
     */
    private function recurseCableRun($connectorID, &$cableRun = [])
    {
        if ($connectorID)
        {
            $l_assigned_connector = $this->cableConnectionDao->get_assigned_connector($connectorID)
                ->__to_array();

            if ($l_assigned_connector)
            {
                if (isset($l_assigned_connector["isys_catg_connector_list__id"]) && !is_null($l_assigned_connector["isys_catg_connector_list__id"]))
                {
                    // Add Connector to array structure.
                    $cableRun[] = $this->formatConnector(
                        $l_assigned_connector,
                        []
                    );

                    if ($l_assigned_connector['isys_catg_connector_list__type'] == C__CONNECTOR__INPUT)
                    {
                        // Go further with it's siblings (inputs can have more than one)
                        $siblings = $this->connectorDao->get_sibling_by_connector($l_assigned_connector["isys_catg_connector_list__id"]);

                        // If we have more than one sibling, we have to create new rows in the cableRun array
                        if ($siblings->num_rows())
                        {
                            $newRun = [];
                            while ($sibRow = $siblings->get_row())
                            {
                                $siblingThread = [];

                                if ($sibRow["isys_catg_connector_list__id"])
                                {
                                    // Sibling
                                    $this->recurseCableRun($sibRow["isys_catg_connector_list__id"], $siblingThread);
                                } // if

                                $newRun[] = $siblingThread;
                            }
                            $cableRun[count($cableRun) - 1]['multiple'] = $newRun;
                        }
                    }
                    else
                    {
                        // Go further with it's sibling
                        if ($l_assigned_connector["isys_catg_connector_list__isys_catg_connector_list__id"])
                        {
                            // Sibling
                            $this->recurseCableRun($l_assigned_connector["isys_catg_connector_list__isys_catg_connector_list__id"], $cableRun);
                        } // if
                    }

                } // if
            } // if
        }

        return $cableRun;
    } // function

    /**
     * @param   \isys_component_database            $p_db
     * @param   \isys_cmdb_dao_category_g_connector $connectorDao
     * @param   \isys_cmdb_dao_cable_connection     $cableDao
     *
     * @throws  \isys_exception_general
     */
    public function __construct(\isys_component_database $p_db, \isys_cmdb_dao_category_g_connector $connectorDao = null, \isys_cmdb_dao_cable_connection $cableDao = null)
    {
        parent::__construct($p_db);

        // Initialize database objects.
        if (!$connectorDao)
        {
            $this->connectorDao = \isys_cmdb_dao_category_g_connector::instance($this->get_database_component());
        }
        else
        {
            $this->connectorDao = $connectorDao;
        } // if

        if (!$cableDao)
        {
            $this->cableConnectionDao = \isys_cmdb_dao_cable_connection::instance($this->get_database_component());
        }
        else
        {
            $this->cableConnectionDao = $cableDao;
        } // if
    } // function
} // class