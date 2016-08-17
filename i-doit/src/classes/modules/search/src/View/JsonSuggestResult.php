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
namespace idoit\Module\Search\View;

use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Search\Query\Protocol\QueryResult;
use idoit\View\Base;
use idoit\View\Renderable;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class JsonSuggestResult extends Base implements Renderable
{
    /**
     * Search result data
     *
     * @var QueryResult
     */
    private $data;

    /**
     * @var string
     */
    private $searchString = '';

    /**
     * @param QueryResult $data
     *
     * @return $this
     */
    public function setData(QueryResult $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $searchString
     *
     * @return $this
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;

        return $this;
    }

    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        try
        {
            /**
             * Check access rights
             */
            \isys_auth_search::instance()
                ->check(\isys_auth::EMPTY_ID_PARAM, "search");

            $data = [];
            foreach ($this->data->getResult() as $item)
            {
                $data[] = [
                    'source' => ucfirst($item->getType()) . ': ' . $item->getKey(),
                    'value'  => $item->getValue(),
                    'link'   => $item->getLink(),
                    'score'  => number_format(floatval($item->getScore()), 2)
                ];
            }

            \isys_core::send_header('Content-Type', 'application/json');
            echo \isys_format_json::encode($data);
            die;

        }
        catch (\isys_exception_auth $e)
        {
            $p_template->assign("exception", $e->write_log());
            $p_template->include_template('contentbottomcontent', 'exception-auth.tpl');
        }

        return $this;
    }

    /**
     * @param array $p_row
     */
    public function rowModifier(&$p_row)
    {
        try
        {
            $p_row['value'] = '<a href="' . $p_row['link'] . '">' . $p_row['value'] . '</a>';
        }
        catch (\Exception $e)
        {

        }
    }

}