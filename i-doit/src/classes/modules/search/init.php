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
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.5.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

if (include_once('isys_module_search_autoload.class.php'))
{
    spl_autoload_register('isys_module_search_autoload::init');
}

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\Search', __DIR__ . '/src/');

/**
 * Register index ovservers
 */
\idoit\Module\Search\Index\Observer\ObserverRegistry::register(
    'MySQL',
    function ()
    {
        //Attach cmdb observer to create mysql index
        return new \idoit\Module\Search\Index\Observer\Mysql(
            isys_application::instance()->container, false
        );
    }
);


/**
 * @var $g_comp_template_language_manager isys_component_template_language_manager
 */
global $g_comp_template_language_manager;
$g_comp_template_language_manager->append_lang_file(
    __DIR__ . DS . 'lang' . DS . isys_component_session::instance()
        ->get_language() . '.inc.php'
);

isys_tenantsettings::extend(
    [
        _L('LC__UNIVERSAL__SEARCH') => [
            'defaults.search.mode' => [
                'title'       => _L('LC__SEARCH__CONFIG__MODE'),
                'type'        => 'select',
                'options'     => \idoit\Module\Search\Query\Condition::$modes,
                'description' => _L('LC__SEARCH__CONFIG__SUGGESTION_NOTE') . '<br /><br />' . _L('LC__SEARCH__CONFIG__NORMAL_DESCRIPTION') . '<br /><br />' .
                    _L('LC__SEARCH__CONFIG__FUZZY_DESCRIPTION') . '<br /><br />' . _L('LC__SEARCH__CONFIG__DEEP_DESCRIPTION'),
                'default'     => '0',
            ]
        ]
    ]
);