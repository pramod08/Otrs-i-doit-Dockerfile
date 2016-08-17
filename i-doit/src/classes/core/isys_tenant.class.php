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

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_tenant
{

    /**
     * @var string
     */
    public $cache_dir = '';
    /**
     * @var string
     */
    public $database = '';
    /**
     * @var string
     */
    public $description = '';
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name = '';

    /**
     * @param string $p_name
     * @param string $p_description
     * @param int    $p_id
     * @param string $p_database
     * @param string $p_cache_dir
     */
    public function __construct($p_name, $p_description, $p_id, $p_database, $p_cache_dir)
    {
        $this->name        = $p_name;
        $this->description = $p_description;
        $this->id          = (int) $p_id;
        $this->database    = $p_database;
        $this->cache_dir   = $p_cache_dir;
    }
}