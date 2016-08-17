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
namespace idoit\Module\Cmdb\Search\IndexExtension;

/**
 * i-doit
 *
 * Cmdb Index Manager Config
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Config
{
    /**
     * Specify the categories to be blacklisted
     *
     * @var string[] Category constants
     */
    private $categoryBlacklist = [];

    /**
     * Specify the object ids to be indexed. Empty array for all objects.
     *
     * @var array
     */
    private $objectIds = [];

    /**
     * Specify the object types to be excluded from index
     *
     * @var array
     */
    private $objectTypeBlacklist = [];

    /**
     * @var array
     */
    private $providesFlags = [];

    /**
     * @return array
     */
    public function getProvidesFlags()
    {
        return $this->providesFlags;
    }

    /**
     * @param array $providesFlags
     *
     * @return $this
     */
    public function setProvidesFlags($providesFlags)
    {
        $this->providesFlags = $providesFlags;

        return $this;
    }

    /**
     * @return array
     */
    public function getObjectTypeBlacklist()
    {
        return $this->objectTypeBlacklist;
    }

    /**
     * @param array $objectTypeBlacklist
     *
     * @return $this
     */
    public function setObjectTypeBlacklist($objectTypeBlacklist)
    {
        $this->objectTypeBlacklist = $objectTypeBlacklist;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategoryBlacklist()
    {
        return $this->categoryBlacklist;
    }

    /**
     * @param array $categoryBlacklist
     *
     * @return $this
     */
    public function setCategoryBlacklist($categoryBlacklist)
    {
        $this->categoryBlacklist = $categoryBlacklist;

        return $this;
    }

    /**
     * @return array
     */
    public function getObjectIds()
    {
        return $this->objectIds;
    }

    /**
     * @param array $objectIds
     *
     * @return $this
     */
    public function setObjectIds($objectIds)
    {
        $this->objectIds = $objectIds;

        return $this;
    }

    /**
     * Config constructor.
     *
     * @param array $objectTypeBlacklist
     * @param array $categoryBlacklist
     * @param array $objectRange
     */
    public function __construct($objectTypeBlacklist = [], $categoryBlacklist = [], $objectRange = [], $providesFlags = [])
    {
        $this->objectTypeBlacklist = $objectTypeBlacklist;
        $this->objectIds           = $objectRange;
        $this->providesFlags       = $providesFlags;

        foreach ($categoryBlacklist as $key => $value)
        {
            if (is_numeric($key) && defined($value))
            {
                $this->categoryBlacklist[$value] = constant($value);
            }
            else
            {
                if (defined($key))
                {
                    $this->categoryBlacklist[$key] = constant($key);
                }
            }
        }
    }

}