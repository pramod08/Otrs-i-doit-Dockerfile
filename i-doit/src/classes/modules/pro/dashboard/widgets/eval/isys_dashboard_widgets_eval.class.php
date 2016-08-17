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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_eval extends isys_dashboard_widgets
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    } // function

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        isys_core::expire(300);

        $this->m_tpl_file        = __DIR__ . '/templates/eval.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    } // function

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_rules = [
            'layout'     => $this->m_config['layout'],
            'short_form' => $this->m_config['short_form']
        ];

        $l_layout_options = [
            'vertical'   => _L('LC__WIDGET__EVAL__LAYOUT_VERTITAL'),
            'horizontal' => _L('LC__WIDGET__EVAL__LAYOUT_HORIZONTAL')
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__EVAL__CONFIG'))
            ->assign('layout_options', serialize($l_layout_options))
            ->assign('short_form_options', serialize(get_smarty_arr_YES_NO()))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        global $g_comp_database_system, $g_comp_session;

        try
        {
            $l_locales = isys_locale::get_instance();
        }
        catch (Exception $e)
        {
            $l_locales = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());
        } // try

        $l_module = new isys_module_licence();
        $l_module->init(isys_module_request::get_instance());
        $l_remaining_days = false;

        $l_licences = $l_module->get_installed_licences(
            $g_comp_database_system,
            $g_comp_session->get_current_mandator_as_id()
        );

        foreach ($l_licences as &$l_licence)
        {
            // Prepare object data.
            $l_licence['remaining_objects'] = $l_licence['objcount'] - $l_licence['in_use'];

            $l_licence['remaining_objects_percent'] = 0;

            // Preventing division by zero.
            if ($l_licence['objcount'] > 0)
            {
                $l_licence['remaining_objects_percent'] = round(($l_licence['in_use'] / $l_licence['objcount']) * 100, 2);
            } // if

            // Handle the object logic.
            if ($l_licence['unlimited'])
            {
                $l_licence['string_obj_limit'] = _L('LC__WIDGET__EVAL__OBJ_UNLIMITED');
            }
            else
            {
                if ($l_licence['remaining_objects_percent'] >= 100)
                {
                    $l_licence['remaining_objects_percent'] = 100;
                    $l_licence['string_obj_limit']          = _L('LC__WIDGET__EVAL__OBJ_LIMIT_EXCEEDED', ($l_licence['remaining_objects'] * -1));
                }
                else
                {
                    if ($this->m_config['short_form'])
                    {
                        $l_licence['string_obj_limit'] = _L('LC__WIDGET__EVAL__OBJ_LIMIT_SHORT', [$l_licence['remaining_objects']]);
                    }
                    else
                    {
                        $l_licence['string_obj_limit'] = _L(
                            'LC__WIDGET__EVAL__OBJ_LIMIT',
                            [
                                $l_licence['objcount'],
                                $l_licence['in_use'],
                                $l_licence['remaining_objects']
                            ]
                        );
                    } // if
                } // if
            } // if

            // "Buyers" licences have no expiration.
            if ($l_licence['type'] != C__LICENCE_TYPE__BUYERS_LICENCE && $l_licence['type'] != C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING && $l_licence['expires'] > 0)
            {
                // Prepare date data.
                $l_leftover                          = $l_licence['expires'] - $l_licence['reg_date'];
                $l_licence['remaining_time_percent'] = 100;

                if ($l_leftover > 0)
                {
                    $l_licence['remaining_time_percent'] = round(((time() - $l_licence['reg_date']) / $l_leftover) * 100, 2);
                } // if

                $l_licence['requested_at'] = $l_locales->fmt_date($l_licence['reg_date'], false);
                $l_licence['expires_at']   = $l_locales->fmt_date($l_licence['expires'], false);
                $l_remaining_days          = round(($l_licence['expires'] - time()) / isys_convert::DAY);

                // We only want to display the "remaining days" when the licence expires in less than a month.
                if ($l_remaining_days < 30)
                {
                    $l_remaining_days = $l_remaining_days . ' ' . (($l_remaining_days == 1) ? _L('LC__WIDGET__EVAL__TIME_DAY') : _L('LC__WIDGET__EVAL__TIME_DAYS')) . ' ' . _L(
                            'LC__WIDGET__EVAL__TIME_REMAINING'
                        );
                }
                else
                {
                    $l_remaining_days = '';
                } // if

                // Handle the licence date logic.
                if ($l_licence['remaining_time_percent'] >= 100)
                {
                    $l_remaining_time = isys_glob_date_diff($l_licence['expires'], null, 'ymd');

                    $l_rendered_time = $l_remaining_time['y'] . ' ' . ($l_remaining_time['y'] == 1 ? _L('LC__WIDGET__EVAL__TIME_YEAR') : _L(
                            'LC__WIDGET__EVAL__TIME_YEARS'
                        )) . ', ' . $l_remaining_time['m'] . ' ' . ($l_remaining_time['m'] == 1 ? _L('LC__WIDGET__EVAL__TIME_MONTH') : _L(
                            'LC__WIDGET__EVAL__TIME_MONTHS'
                        )) . ' ' . _L('LC__UNIVERSAL__AND') . ' ' . $l_remaining_time['d'] . ' ' . ($l_remaining_time['d'] == 1 ? _L('LC__WIDGET__EVAL__TIME_DAY') : _L(
                            'LC__WIDGET__EVAL__TIME_DAYS'
                        ));

                    $l_licence['remaining_time_percent'] = 100;

                    if ($this->m_config['short_form'])
                    {
                        $l_licence['string_time_limit'] = _L('LC__WIDGET__EVAL__TIME_LIMIT_EXCEEDED_SHORT', [$l_rendered_time]);
                    }
                    else
                    {
                        $l_licence['string_time_limit'] = _L(
                            'LC__WIDGET__EVAL__TIME_LIMIT_EXCEEDED',
                            [
                                $l_licence['requested_at'],
                                $l_licence['expires_at'],
                                $l_rendered_time
                            ]
                        );
                    } // if
                }
                else
                {
                    $l_remaining_time = isys_glob_date_diff(null, $l_licence['expires'], 'ymd');
                    $l_rendered_time  = '';

                    if ($l_remaining_time['y'] > 0) $l_rendered_time .= $l_remaining_time['y'] . ' ' . ($l_remaining_time['y'] == 1 ? _L('LC__WIDGET__EVAL__TIME_YEAR') : _L(
                            'LC__WIDGET__EVAL__TIME_YEARS'
                        )) . ', ';

                    if ($l_remaining_time['m'] > 0) $l_rendered_time .= $l_remaining_time['m'] . ' ' . ($l_remaining_time['m'] == 1 ? _L('LC__WIDGET__EVAL__TIME_MONTH') : _L(
                            'LC__WIDGET__EVAL__TIME_MONTHS'
                        )) . ' ' . _L('LC__UNIVERSAL__AND') . ' ';

                    $l_rendered_time .= $l_remaining_time['d'] . ' ' . ($l_remaining_time['d'] == 1 ? _L('LC__WIDGET__EVAL__TIME_DAY') : _L('LC__WIDGET__EVAL__TIME_DAYS'));

                    if ($this->m_config['short_form'])
                    {
                        $l_licence['string_time_limit'] = _L('LC__WIDGET__EVAL__TIME_LIMIT_SHORT', [$l_rendered_time]);
                    }
                    else
                    {
                        $l_licence['string_time_limit'] = _L(
                            'LC__WIDGET__EVAL__TIME_LIMIT',
                            [
                                $l_licence['requested_at'],
                                $l_licence['expires_at'],
                                $l_rendered_time
                            ]
                        );
                    } // if
                } // if
            }
            else
            {
                $l_licence['string_time_limit'] = _L('LC__WIDGET__EVAL__TIME_LIMIT_BUYERS');
            } // if
        } // foreach

        return $this->m_tpl->assign('uid', $p_unique_id)
            ->assign('layout', $this->m_config['layout'])
            ->assign('licences', $l_licences)
            ->assign('remaining_days', $l_remaining_days)
            ->fetch($this->m_tpl_file);
    } // function
} // class