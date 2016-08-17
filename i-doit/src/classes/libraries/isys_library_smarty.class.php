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
 * i-doit
 *
 * Smarty Wrapper - implements the Smarty API
 * But remember, this is something like an
 * abstract library integration layer.
 *
 * Note: Smarty is loaded via composer.
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.3
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_library_smarty extends Smarty
{
    /**
     * Registers object to be used in templates
     *
     * @param string  $object       name of template object
     * @param object  $object_impl  the referenced PHP object to register
     * @param array   $allowed      list of allowed methods (empty = all)
     * @param boolean $smarty_args  smarty argument format, else traditional
     * @param array   $block_functs list of methods that are block format
     *
     * @return $this
     */
    public function register_object($object, $object_impl, $allowed = [], $smarty_args = true, $block_methods = [])
    {
        settype($allowed, 'array');
        settype($smarty_args, 'boolean');
        $this->registerObject($object, $object_impl, $allowed, $smarty_args, $block_methods);

        return $this;
    }

    /**
     * @param array $p_options
     *
     * @throws SmartyException
     */
    public function __construct($p_options = [])
    {
        if (class_exists('Memcache'))
        {
            include_once(dirname(__FILE__) . "/smarty/cacheresource.memcache.php");
            $this->registerCacheResource('memcache', new Smarty_CacheResource_Memcache());
        }
        parent::__construct($p_options);

        $this->addPluginsDir(__DIR__ . '/smarty/plugins/');
    }
} // class