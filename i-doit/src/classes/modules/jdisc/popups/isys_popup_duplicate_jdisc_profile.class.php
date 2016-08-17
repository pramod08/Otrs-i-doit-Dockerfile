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
 * Popup for JDisc profile duplication
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_duplicate_jdisc_profile extends isys_component_popup
{

    /**
     * Instance of module DAO.
     *
     * @var  isys_module_dao_jdisc
     */
    protected $m_dao;
    /**
     * Instance of database component
     *
     * @var isys_component_database
     */
    protected $m_db;
    /**
     * Instance of logger.
     *
     * @var  isys_log
     */
    protected $m_log;
    protected $m_module = 'jdisc';
    protected $m_type = 'profile';

    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_config;

        $l_url = $g_config['startpage'] . '? ' . 'mod=jdisc&' . C__CMDB__GET__POPUP . '=duplicate_jdisc_profile&' . C__CMDB__GET__EDITMODE . '=' . C__EDITMODE__ON . '&' . C__CMDB__GET__OBJECTTYPE . '=' . $_GET[C__CMDB__GET__OBJECTTYPE];

        $this->set_config('width', 1000);
        $this->set_config('height', 1000);
        $this->set_config('scrollbars', 'no');

        return $this->process($l_url, true);
    } // function

    /**
     * Handles module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Prepare template for popup:
        $l_tplpopup = isys_component_template::instance();
        $l_tplpopup->assign('file_body', 'popup/duplicate_jdisc_profile.tpl');
        $l_tplpopup->activate_editmode();

        try
        {
            $l_posts = $p_modreq->get_posts();
            $l_ids   = [];
            if (!isset($l_posts['id']) || !is_array($l_posts['id']) || count($l_posts['id']) === 0)
            {
                throw new Exception(
                    _L('LC__MODULE__JDISC__POPUP__ERROR__NO_SELECTED_PROFILE')
                );
            } //if

            $l_string_to_int = function ($p_string)
            {
                return (int) $p_string;
            };
            $l_ids           = array_map($l_string_to_int, $l_posts['id']);

            $l_selections   = [
                'id',
                'title'
            ];
            $l_all_profiles = $this->m_dao->get_profiles($l_selections);
            $l_profiles     = [];

            foreach ($l_all_profiles as $l_profile)
            {
                if (in_array($l_profile['id'], $l_ids))
                {
                    $l_profiles[] = [
                        'id'    => 'C__PROFILE__' . $l_profile['id'],
                        'title' => $l_profile['title']
                    ];
                } //if
            } //foreach

            if (count($l_profiles) === 0)
            {
                throw new isys_exception_general(
                    _L('No profile found.')
                );
            } //if

            return $l_tplpopup->assign('profiles', $l_profiles);
        }
        catch (Exception $e)
        {
            return $l_tplpopup->assign('error', $e->getMessage());
        } //try/catch
    } // function

    /**
     * Constructor
     *
     * @global isys_component_database $g_comp_database Database component
     */
    public function __construct()
    {
        global $g_comp_database;
        $this->m_db  = $g_comp_database;
        $this->m_log = isys_factory_log::get_instance($this->m_module);
        $this->m_dao = new isys_jdisc_dao($this->m_db, $this->m_log);

        parent::__construct();
    } //function

} // class

?>