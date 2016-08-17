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
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_rss extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     */
    public function has_configuration()
    {
        return true;
    } // function

    /**
     * Init method.
     *
     * @return  isys_dashboard_widgets_quicklaunch
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'rss.tpl';
        $this->m_config_tpl_file = __DIR__ . DS . 'templates' . DS . 'config.tpl';

        return parent::init($p_config);
    } // function

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_rules = [
            'url'   => $this->m_config['url'],
            'count' => $this->m_config['count']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__RSS__CONFIG'))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     */
    public function render($p_unique_id)
    {
        global $g_absdir, $g_config;

        $l_rssLibrary = new isys_library_simplepie();
        $l_rssLibrary->set_feed_url($this->m_config['url']);
        $l_rssLibrary->set_item_limit($this->m_config['count']);
        $l_rssLibrary->set_cache_location($g_absdir . '/temp');
        $l_rssLibrary->set_useragent(SIMPLEPIE_NAME . '/' . SIMPLEPIE_VERSION . ' via i-doit (Feed Parser; ' . SIMPLEPIE_URL . '; Allow like Gecko) Build/' . SIMPLEPIE_BUILD);
        $l_rssLibrary->set_output_encoding($g_config['html-encoding']);

        if (isys_settings::get('proxy.active', false))
        {
            $l_rssLibrary->set_proxy(isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));
        }

        $l_rssLibrary->init();

        return $this->m_tpl->assign('count', $this->m_config['count'])
            ->assign(
                'dateFormat',
                isys_locale::get_instance()
                    ->get_date_format()
            )
            ->assign('rss', $l_rssLibrary)
            ->fetch($this->m_tpl_file);
    } // function
} // class