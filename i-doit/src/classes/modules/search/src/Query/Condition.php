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
namespace idoit\Module\Search\Query;

/**
 * i-doit
 *
 * Search index condition
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Condition
{

    /**
     * Default search mode
     */
    const MODE_DEFAULT = 1;

    /**
     * Fuzzy search mode
     */
    const MODE_FUZZY = 2;

    /**
     * Deep and slow search mode with partial matching enabled
     */
    const MODE_DEEP = 3;

    /**
     * All modes with string representation
     *
     * @var array
     */
    public static $modes = [
        self::MODE_DEFAULT => 'Normal',
        self::MODE_FUZZY => 'Fuzzy Search',
        self::MODE_DEEP => 'Deep Search'
    ];

    /**
     * @var string
     */
    protected $condition;

    /**
     * Do a fuzzy search on this keyword?
     *
     * @var int
     */
    protected $mode;

    /**
     * Search keyword
     *
     * @var string
     */
    protected $keyword;

    /**
     * Negate this condition?
     *
     * @var bool
     */
    protected $negation;

    /**
     * Search search operation mode
     *
     * @param int $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $mode = (int) $mode;

        if ($mode >= 1 AND $mode <= 3)
        {
            $this->mode = $mode;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return bool
     */
    public function isNegation()
    {
        return $this->negation;
    }

    /**
     * Condition constructor.
     *
     * @param        $keyword
     * @param string $condition
     * @param bool   $negation
     * @param bool   $fuzzySearch
     */
    public function __construct($keyword, $condition = 'AND', $negation = false, $mode = self::MODE_DEFAULT)
    {
        $this->keyword   = $keyword;
        $this->condition = $condition;
        $this->negation  = $negation;

        $this->setMode($mode);
    }
}