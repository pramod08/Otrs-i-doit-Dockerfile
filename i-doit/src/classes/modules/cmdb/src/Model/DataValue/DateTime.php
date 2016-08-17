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
class DateTime extends BaseValue implements DataValueInterface
{
    /**
     * @var \DateTime
     */
    protected $value = '';

    /**
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \DateTime $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_a($value, '\DateTime'))
        {
            throw new \InvalidArgumentException('Value has to be of type "\DateTime".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (is_object($this->value))
        {
            return $this->value->format('Y-m-d H:i:s');
        }
        else return '';
    }

    /**
     * DateTime constructor.
     *
     * @param \DateTime $value
     */
    public function __construct(\DateTime $value)
    {
        $this->setValue($value);
    }
}