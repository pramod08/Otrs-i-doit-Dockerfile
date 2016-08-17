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

class CiType
{
    /**
     * @var string
     */
    public $const = '';
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title;

    /**
     * @param $id
     * @param $title
     * @param $const
     *
     * @return CiType
     */
    public static function factory($id, $title, $const)
    {
        if ($id > 0)
        {
            $object     = new self();
            $object->id = $id;

            if ($title === null)
            {
                $title = \isys_cmdb_dao::instance(\isys_application::instance()->database)
                    ->get_objTypeID($id);
            }

            $object->title = $title;
            $object->const = $const;

            return $object;
        }
        else throw new \InvalidArgumentException('Could not instantiate CiType. Given ID is invalid.');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}