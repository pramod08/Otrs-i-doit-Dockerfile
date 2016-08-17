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
define('C__LOGINVENTORY__MODE__UPDATE', 1);
define('C__LOGINVENTORY__MODE__CREATE', 2);

/**
 * i-doit
 *
 * Loginventory data module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_loginventory_dao_data extends isys_module_dao
{
    protected $m_category_mapping = [
        'CONTROLLER'     => false,
        'CPU'            => false,
        'DRIVE'          => false,
        'LOCALUSER'      => false,
        'MONITOR'        => false,
        'NET'            => false,
        'OS'             => false,
        // Special
        'PCINFO'         => false,
        'PRINTER'        => false,
        'RAM'            => false,
        'SERVICE'        => false,
        'SHARE'          => false,
        'SOFTWARE'       => false,
        // Special
        'SOUND'          => false,
        'SYSTEM'         => false,
        'VID'            => false,
        'VIRTUALSYSTEM'  => false,
        'PRINTSRVMARKER' => false
        // INDICATOR FOR OBJECTTYPE PRINTER
    ];
    /**
     * Determine if logbook entries should be created or not
     *
     * @var bool
     */
    protected $m_logbook_active = true;
    /**
     * Object ids in loginventory
     *
     * @var array
     */
    protected $m_loginventory_ids = [];
    /**
     * Variable for using the PDO in every child-class without creating a new instance.
     *
     * @var  isys_component_database_pdo
     */
    protected $m_pdo;
    protected $m_table_mapping = [
        'CONTROLLER'    => 'LI_CONTROLLER',
        'CPU'           => 'LI_CPU',
        'DRIVE'         => 'LI_DRIVE',
        'LOCALUSER'     => 'LI_LOCALUSER',
        'MONITOR'       => 'LI_MONITOR',
        'NET'           => 'LI_NET',
        'OS'            => 'LI_OS',
        'PCINFO'        => 'LI_PCINFO',
        'PRINTER'       => 'LI_PRINTER',
        'RAM'           => 'LI_RAM',
        'SERVICE'       => 'LI_SERVICE',
        'SHARE'         => 'LI_SHARE',
        'SOFTWARE'      => 'LI_SOFTWARE',
        'SOUND'         => 'LI_SOUND',
        'SYSTEM'        => 'LI_SYSTEM',
        'VID'           => 'LI_VID',
        'VIRTUALSYSTEM' => 'LI_VIRTUALSYSTEM'
    ];

    public function get_category_mapping()
    {
        return $this->m_category_mapping;
    }

    /**
     * Unused.
     */
    public function get_data()
    {
        // Unused.
    } // function

    /**
     * Gets all clients from loginventory database
     *
     * @return array
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_loginventory_objects($p_id = null, $p_order = 'ASC')
    {
        $l_sql = 'SELECT DISTINCT(main.LA_ID), pci.LI_PCNAME, pci.LI_DN, pci.LI_CATEGORY,net.LI_MACADDRESS, net.LI_IPADDRESS FROM LA_INDEX AS main ' . 'INNER JOIN LI_PCINFO AS pci ON pci.LA_ID = main.LA_ID AND pci.LI_PCNAME = main.LA_UID ' . 'INNER JOIN LI_NET AS net ON net.LA_ID = main.LA_ID AND net.LA_INDEX = 1';

        if (!is_null($p_id))
        {
            $l_sql .= ' WHERE main.LA_ID = \'' . $p_id . '\'';
        }

        $l_sql .= ' ORDER BY pci.LI_PCNAME ' . $p_order;

        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {
            $l_ip = explode(" ", $l_row['LI_IPADDRESS']);
            $l_ip = array_shift($l_ip);
            if (!array_key_exists($l_row['LA_ID'], $l_data) && $l_row['LI_PCNAME'] != '')
            {
                $l_data[$l_row['LA_ID']] = $l_row;
                unset($l_data[$l_row['LA_ID']]['LI_IPADDRESS']);
                $l_data[$l_row['LA_ID']]['IP'][] = $l_ip;
            }
            elseif ($l_data[$l_row['LA_ID']]['LI_DN'] == '' && $l_row['LI_DN'] != '')
            {
                $l_data[$l_row['LA_ID']]['LI_DN']       = $l_row['LI_DN'];
                $l_data[$l_row['LA_ID']]['LI_CATEGORY'] = $l_row['LI_CATEGORY'];
            }

            if (!in_array($l_ip, $l_data[$l_row['LA_ID']]['IP']))
            {
                $l_data[$l_row['LA_ID']]['IP'][] = $l_ip;
            }
        }

        return $l_data;
    }

    /**
     * Get information for global category cpu
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_cpu($p_id)
    {
        $l_data = [];
        if (is_numeric($p_id) && $p_id > 0)
        {
            $l_sql = 'SELECT * FROM LI_CPU WHERE LA_ID = \'' . $p_id . '\'';
            $l_res = $this->m_pdo->query($l_sql);

            $l_row = $this->m_pdo->fetch_row_assoc($l_res);

            if ($l_row['LA_ID'] > 0)
            {
                for ($i = 0;$i < $l_row['LI_PROCESSORS'];$i++)
                {
                    /*$l_description = ''.$l_row['LI_TYPE']."\n".
                        ''.$l_row['LI_SUBTYPE']."\n";*/
                    $l_data[] = [
                        'title'        => trim($l_row['LI_NAME']),
                        'manufacturer' => trim($l_row['LI_NAME']),
                        'type'         => trim($l_row['LI_TYPE']) . ' ' . trim($l_row['LI_SUBTYPE']),
                        'frequency'    => ((float) $l_row['LI_MHZ'] / 1000),
                        // Hertz
                        'unit'         => 'GHz',
                        'cores'        => trim($l_row['LI_PROCESSORS'])
                        //'description' => $l_description
                    ];
                }
            }
        }

        return $l_data;
    }

    /**
     * Get information for global category model
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_model($p_id)
    {
        $l_data = [];
        if (is_numeric($p_id) && $p_id > 0)
        {
            $l_sql = 'SELECT * FROM LI_SYSTEM WHERE LA_ID = \'' . $p_id . '\'';
            $l_res = $this->m_pdo->query($l_sql);

            $l_row = $this->m_pdo->fetch_row_assoc($l_res);

            if ($l_row['LA_ID'] > 0)
            {
                $l_description = '' . $l_row['LI_VENDOR'] . "\n" . '' . $l_row['LI_VERSION'] . "\n" . '' . $l_row['LI_BBMANUFACTURER'] . "\n" . '' . $l_row['LI_BBSERIAL'];
                $l_data        = [
                    'manufacturer' => trim($l_row['LI_MANUFACTURER']),
                    'title'        => trim($l_row['LI_PRODUCTNAME']),
                    'service_tag'  => trim($l_row['LI_SERIAL']),
                    'description'  => $l_description
                ];
            }
        }

        return $l_data;
    }

    /**
     * Get information for global category application (Applications)
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_application($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_SOFTWARE WHERE LA_ID = \'' . $p_id . '\' AND LI_KEYNAME NOT LIKE \'LPCINFO_OSVER_GENERATED\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {
            $l_description = $l_row['LI_INSTALLDATE'];

            $l_data[] = [
                'application'          => trim($l_row['LI_DISPNAME']),
                'manufacturer'         => trim($l_row['LI_PUBLISHER']),
                'release'              => trim($l_row['LI_VERSION']),
                'description_specific' => $l_description,
                'description'          => trim($l_row['LI_LANGUAGE']),
                'assigned_license'     => trim($l_row['LI_PRODUCTKEY'])
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category application (Services)
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_service($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_SERVICE WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {
            $l_description = $l_row['LI_PATH'] . "\n" . $l_row['LI_STARTMODE'] . "\n" . $l_row['LI_ACCOUNT'] . "\n" . $l_row['LI_DISPNAME'];

            $l_data[] = [
                'application' => trim($l_row['LI_NAME']),
                'description' => $l_description,
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category memory
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_memory($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_RAM WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_data[] = [
                'title'    => trim($l_row['LI_NAME']),
                'type'     => trim($l_row['LI_MEMORYTYPE']),
                'capacity' => trim($l_row['LI_SIZE']),
                'unit'     => C__MEMORY_UNIT__MB
            ];
        }

        return $l_data;
    }

    /**
     * * Get information for global category shares
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_share($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_SHARE WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_description = $l_row['LI_TYPE'] . "\n" . $l_row['LI_DESCRIPTION'];

            $l_data[] = [
                'title'       => trim($l_row['LI_NAME']),
                'path'        => trim($l_row['LI_PATH']),
                'description' => $l_description
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category sound
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_sound($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_SOUND WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_data[] = [
                'title' => trim($l_row['LI_NAME']),
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category controller
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_controller($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_CONTROLLER WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_data[] = [
                'title' => trim($l_row['LI_NAME']),
                'type'  => trim($l_row['LI_TYPE']),
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category contact
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_localuser($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_LOCALUSER WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_contact = explode('\\', trim($l_row['LI_DISPLAYNAME']));

            $l_data[] = [
                'contact' => $l_contact[1],
                'role'    => 'LC__CMDB__LOGBOOK__USER'
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category application (OS)
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_os($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_OS WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_data[] = [
                'application'      => trim($l_row['LI_TYPE']),
                'release'          => trim($l_row['LI_VERSION']),
                'description'      => trim($l_row['LI_LANGUAGE']),
                'assigned_license' => trim($l_row['LI_PRODUCTKEY'])
            ];
        }

        return $l_data;
    }

    /**
     * Get information for global category graphic
     *
     * @param $p_id
     *
     * @return array
     */
    public function prepare_graphic($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_VID WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            $l_data[] = [
                'title'  => trim($l_row['LI_ADAPTERTYPE']),
                'memory' => trim($l_row['LI_ADAPTERMEM']),
                'unit'   => C__MEMORY_UNIT__MB
            ];
        }

        return $l_data;
    }

    public function prepare_storage($p_id)
    {
        $l_sql  = 'SELECT * FROM LI_DRIVE WHERE LA_ID = \'' . $p_id . '\'';
        $l_res  = $this->m_pdo->query($l_sql);
        $l_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {

            if ($l_row['LI_SIZE'] > 0) $l_capacity = (((int) trim($l_row['LI_SIZE'])) / 1024) / 1024;
            else $l_capacity = 0;

            $l_type = trim($l_row['LI_TYPE']);

            switch ($l_type)
            {
                case 'CD-ROM-Laufwerk':
                case 'CD-ROM-Drive':
                case 'CD-ROM Drive':
                    $l_type = 'LC__STORAGE_TYPE__CD_ROM';
                    break;
                case 'Floppy disk drive':
                case 'Diskettenlaufwerk':
                    $l_type = 'LC__STORAGE_TYPE__FLOPPY';
                    break;
                case 'Disk drive':
                case 'Laufwerk':
                    $l_type = 'LC__STORAGE_TYPE__HARD_DISK';
                    break;
            }

            $l_data[] = [
                'title'    => trim($l_row['LI_NAME']),
                'type'     => $l_type,
                'unit'     => 'GB',
                'capacity' => $l_capacity
                // KB -> GB
            ];
        }

        return $l_data;
    }

    public function prepare_pcinfo($p_id)
    {

    }

    public function prepare_net($p_id)
    {
        $l_sql     = 'SELECT * FROM LI_NET WHERE LA_ID = \'' . $p_id . '\'';
        $l_res     = $this->m_pdo->query($l_sql);
        $l_data    = [];
        $l_counter = 0;
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
        {
            if (!empty($l_row['LI_IFTYPENAME']))
            {
                $l_data[$l_counter]['interface'] = [
                    'title' => $l_row['LI_IFTYPENAME'],
                ];
            }
            if (!empty($l_row['LI_IPADDRESS']))
            {
                $l_ip                     = explode(' ', $l_row['LI_IPADDRESS']);
                $l_dnsserver              = explode(' ', $l_row['LI_DNSSERVER']);
                $l_subnetmask             = explode(' ', $l_row['LI_SUBNETMASK']);
                $l_data[$l_counter]['ip'] = [
                    'title'      => $l_ip,
                    'dnsserver'  => $l_dnsserver,
                    'gateway'    => $l_row['LI_DEFAULTGATEWAY'],
                    'subnetmask' => $l_subnetmask,
                    'index'      => $l_row['LA_INDEX']
                ];
            }
            $l_data[$l_counter]['port'] = [
                'title'      => $l_row['LI_CARDTYPE'],
                'mac'        => $l_row['LI_MACADDRESS'],
                'speed_type' => C__PORT_SPEED__MBIT_S,
                'speed'      => $l_row['LI_SPEED']
            ];
            $l_counter++;
        }

        return $l_data;
    }

    /**
     * Constructor
     *
     * @param   isys_component_database     $p_db Database component
     * @param   isys_component_database_pdo $p_pdo
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        parent::__construct($p_db);

        $this->m_pdo = $p_pdo;
    } // function

} // class
?>