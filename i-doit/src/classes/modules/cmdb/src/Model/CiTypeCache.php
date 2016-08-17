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
namespace idoit\Module\Cmdb\Model;

use idoit\Component\Provider\Singleton;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CiTypeCache
{
    use Singleton;

    /**
     * @var CiType[]
     */
    private $ciTypes = [];

    /**
     * @return CiType[]
     */
    public function getCiTypes()
    {
        return $this->ciTypes;
    }

    /**
     * CiTypeCache constructor.
     *
     * @param \isys_component_database $database
     */
    public function __construct(\isys_component_database $database)
    {
        $types = \isys_cmdb_dao_object_type::instance($database)
            ->get_object_types()
            ->__as_array();

        foreach ($types as $type)
        {
            $this->ciTypes[$type['isys_obj_type__id']] = CiType::factory(
                $type['isys_obj_type__id'],
                _L($type['isys_obj_type__title']),
                $type['isys_obj_type__const']
            );
        }
    }

}