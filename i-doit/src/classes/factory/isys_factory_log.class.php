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
 * Factory for logger.
 *
 * @package     i-doit
 * @subpackage  Log
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_factory_log extends isys_factory
{
    /**
     * Contains all factorized log topics.
     *
     * @var  array  Array of strings
     */
    protected static $m_topics = [];

    /**
     * Gets an instance of a class.
     *
     * @param   string $p_topic  Log topic
     * @param   null   $p_params Unused parameter. Is needed because of strict standards.
     *
     * @return  isys_log
     */
    public static function get_instance($p_topic, $p_params = null)
    {
        global $g_config, $g_product_info;

        $l_object = isys_log::get_instance($p_topic);

        if (!in_array($p_topic, self::$m_topics))
        {
            $l_log_file = $g_config['base_dir'] . 'log/' . $p_topic . '_' . date('Y-m-d_H_i_s') . '.log';
            $l_header   = '# i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['type'] . PHP_EOL . '# host URL ' . C__HTTP_HOST . PHP_EOL . '# log for "' . $p_topic . '"' . PHP_EOL . '# started at ' . date(
                    'c'
                ) . PHP_EOL . '# written to "' . $l_log_file . '"' . PHP_EOL;

            $l_object->set_log_level(isys_log::C__ALL & ~isys_log::C__DEBUG)
                ->set_verbose_level(isys_log::C__FATAL | isys_log::C__ERROR | isys_log::C__WARNING | isys_log::C__NOTICE)
                ->set_log_file($l_log_file)
                ->set_header($l_header);

            self::$m_topics[] = $p_topic;
        } // if

        return $l_object;
    } // function
} // class