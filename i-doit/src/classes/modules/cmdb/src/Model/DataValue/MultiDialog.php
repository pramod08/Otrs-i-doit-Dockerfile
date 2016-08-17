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
namespace idoit\Module\Cmdb\Model\DataValue;

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
class MultiDialog extends BaseValue implements DataValueInterface
{
    /**
     * @var array
     */
    protected $value;

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_array($value))
        {
            throw new \InvalidArgumentException('Value has to be of type "array".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(', ', $this->value);
    }

    /**
     * Ci constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
    {
        $this->setValue($value);
    }
}