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
namespace idoit\Module\Events\Model;

use idoit\Model\Dao\Base;

/**
 * i-doit Events Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Events extends Base
{

    /**
     * Synchronizes database table isys_event with all available events
     */
    public function synchronize()
    {
        $modMan  = new \isys_module_manager($this->m_db);
        $modules = $modMan->get_modules();

        while ($item = $modules->get_row())
        {
            if (isset($item['id']) && isset($item['isys_module__identifier']))
            {
                if (class_exists('isys_module_' . $item['isys_module__identifier']))
                {
                    $moduleClass = 'isys_module_' . $item['isys_module__identifier'];

                    if (is_a($moduleClass, 'isys_module_hookable', true))
                    {
                        /**
                         * @var $moduleInstance \isys_module_hookable
                         */
                        $moduleInstance = new $moduleClass();
                        $this->registerHooks(
                            $item['id'],
                            $moduleInstance->hooks()
                        );
                    }
                }
            }
        }
    }

    /**
     * Register all $hooks
     *
     * @param int         $moduleID
     * @param \isys_array $hooks
     */
    private function registerHooks($moduleID, \isys_array $hooks)
    {
        $this->begin_update();

        foreach ($hooks as $identifier => $hook)
        {
            $this->register(
                $moduleID,
                $identifier,
                $hook['title'],
                $hook['handler'],
                isset($hook['description']) ? $hook['description'] : ''
            );
        }

        $this->apply_update();
    }

    /**
     * Registers an event in database table isys_event
     *
     * @param int    $moduleID
     * @param string $identifier
     * @param string $title
     * @param string $description
     * @param int    $statis
     *
     * @return $this
     */
    private function register($moduleID, $identifier, $title, $handler, $description, $status = C__RECORD_STATUS__NORMAL)
    {
        // Using IGNORE to just skip existing items based on the isys_event__identifier UNUQUE KEY.
        $sql = 'INSERT IGNORE INTO isys_event SET ' . 'isys_event__isys_module__id = ' . $this->convert_sql_id(
                $moduleID
            ) . ', ' . 'isys_event__title = ' . $this->convert_sql_text($title) . ', ' . 'isys_event__handler = ' . $this->convert_sql_text(
                $handler
            ) . ', ' . 'isys_event__identifier = ' . $this->convert_sql_text($identifier) . ', ' . 'isys_event__status = ' . $this->convert_sql_text(
                $status
            ) . ', ' . 'isys_event__description = ' . $this->convert_sql_text($description) . ';';

        $this->update($sql);

        return $this;
    }

}