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
 * Data format.
 *
 * @package     i-doit
 * @subpackage  Data
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_format
{
    /**
     * Encodes a value.
     *
     * @param   mixed $p_value The value being encoded. Can be any type except a resource.
     *
     * @return  string  Encoded value
     */
    abstract public static function encode($p_value);

    /**
     * Decodes a value.
     *
     * @param   string $p_value The string being decoded
     *
     * @return  mixed  Decoded value
     */
    abstract public static function decode($p_value);
} // class