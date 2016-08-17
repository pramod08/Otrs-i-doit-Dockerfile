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
namespace idoit\Module\Search\Controller;

use idoit\Model\Dao\Base;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Engine\Mysql\SearchEngine as MysqlSearchEngine;
use idoit\Module\Search\Query\QueryManager;
use idoit\Module\Search\View\JsonSuggestResult;
use idoit\Module\Search\View\SearchBox;
use idoit\Module\Search\View\SearchResultList;

/**
 * i-doit
 *
 * Global Search Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Main implements \isys_controller
{
    /**
     * @var \isys_module_search
     */
    private $module;

    /**
     * @action /search/reindex
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        try
        {
            $searchString = $_GET['q'] ?: null;

            if ($searchString)
            {
                $mode = isset($_GET['mode']) ? $_GET['mode'] : \isys_tenantsettings::get('defaults.search.mode', Condition::MODE_DEFAULT);

                $manager = QueryManager::factory()
                    ->attachEngine(new MysqlSearchEngine())
                    ->addSearchKeyword($searchString, 'AND', false, $mode);

                if (\isys_core::is_ajax_request())
                {
                    $view = new JsonSuggestResult($p_request);
                    $view->setData($manager->search());
                }
                else
                {
                    if (strlen($searchString) < 3)
                    {
                        throw new \Exception(_L('LC__MODULE__SEARCH__NOTIFY__SEARCHPHRASE_TO_SHORT'));
                    }

                    $view = new SearchResultList($p_request);

                    if (strpos($searchString, 'title:') === 0)
                    {
                        $searchString = str_replace('title:', '', $searchString);

                        $l_cmdb_dao     = new \isys_cmdb_dao($p_application->database);
                        $l_found_object = $l_cmdb_dao->get_obj_id_by_title($searchString);
                        if ($l_found_object > 0)
                        {
                            header('Location: ' . $p_application->www_path . '?objID=' . $l_found_object);
                            die;
                        }
                    }

                    $view->setData($manager->search())
                        ->setSearchMode($mode)
                        ->setSearchString($searchString);
                }

            }
            else
            {
                $view = new SearchBox($p_request);
            }

            // Return the view
            return $view;
        }
        catch (\Exception $e)
        {
            $p_application->instance()->container['notify']->error($e->getMessage());
        }

        return null;
    }

    /**
     * @param \isys_application $p_application
     *
     * @return \isys_cmdb_dao_nexgen
     */
    public function dao(\isys_application $p_application)
    {
        return new Base($p_application->database);
    }

    /**
     * @param \isys_register       $p_request
     * @param \isys_application    $p_application
     * @param \isys_component_tree $p_tree
     *
     * @return null
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        \isys_module_manager::instance()
            ->get_request()
            ->_internal_set_private(
                'm_get',
                [C__CMDB__GET__OBJECTGROUP => C__OBJTYPE_GROUP__INFRASTRUCTURE]
            );
        $treeView = new \isys_cmdb_view_tree_objecttype(
            \isys_module_manager::instance()
                ->get_request()
        );
        $p_application->template->assign('menu_tree', $treeView->process());

        return null;
    }

    /**
     * Index constructor.
     *
     * @param \isys_module_search $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }

}