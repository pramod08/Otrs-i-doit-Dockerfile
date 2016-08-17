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
namespace idoit\Component\Helper;

use idoit\Component\Provider\Singleton;
use idoit\Exception\OutOfMemoryException;

/**
 * i-doit Memory helper
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.7.1
 */
class Memory
{
    use Singleton;

    /**
     * Maximum amount of allocatable memory in bytes
     *
     * @var integer
     */
    protected $maxMemory = 0;

    /**
     * Maximum amount of allocatable memory in bytes
     *
     * @return integer
     */
    public function getMaxMemory()
    {
        return $this->maxMemory;
    }

    /**
     * Return current memory usage in bytes
     *
     * @return integer
     */
    public function getMemoryUsage()
    {
        return memory_get_usage(true);
    }

    /**
     * Return memory peak usage in bytes
     *
     * @return integer
     */
    public function getMemoryPeakUsage()
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Checks wheather the given amount of memory is allocatable or not.
     *
     * @param int $amount Amount of memory to check in bytes.
     *
     * @return boolean
     */
    public function isMemoryAvailable($amount = 1024)
    {
        return !(($this->getMemoryUsage() + $amount) > $this->maxMemory);
    }

    /**
     * @param int $amount Amount of memory to check in bytes.
     *
     * @throws OutOfMemoryException
     */
    public function outOfMemoryBreak($amount = 1024)
    {
        if (!$this->isMemoryAvailable($amount))
        {
            throw new OutOfMemoryException(sprintf('Maximum allowed memory reached. (%s MB). You can increase this limit in your php configuration: http://php.net/manual/en/ini.core.php#ini.memory-limit', $this->maxMemory / 1024 / 1024));
        }
    }

    /**
     * Memory constructor.
     */
    public function __construct()
    {
        $this->maxMemory = Filesize::toBytes(ini_get('memory_limit'));
    }

}