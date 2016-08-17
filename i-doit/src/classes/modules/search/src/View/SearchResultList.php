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
class SearchResultList extends Base implements Renderable
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
     * @var int
     */
    private $searchMode;

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
     * @param int $searchMode
     *
     * @return $this
     */
    public function setSearchMode($searchMode)
    {
        $this->searchMode = $searchMode;

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
     * @param $data
     *
     * @return string
     */
    private function getHtmlList($searchString = '')
    {
        $list = '<table id="mainTable" cellspacing="0" class="mainTable"><tr><th>' . _L('LC__UNIVERSAL__SOURCE') . '</th><th>' . _L(
                'LC__MODULE__SEARCH__FOUND_MATCH'
            ) . '</th><th class="desc">Score</th></tr>';

        foreach ($this->data->getResult() as $item)
        {
            //similar_text($item->getValue(), $searchString, $percent);

            $list .= sprintf(
                '<tr><td>%s</td><td>%s</td><td><div class="progress"><div class="progress-bar" data-width-percent="%s"></div></div></td></tr>',
                ucfirst($item->getType()) . ': ' . $item->getKey(),
                '<a href="' . $item->getLink() . '">' . str_replace(
                    ucfirst($searchString),
                    '<strong>' . ucfirst($searchString) . '</strong>',
                    str_replace($searchString, '<strong>' . $searchString . '</strong>', $item->getValue())
                ) . '</a>',
                floatval($item->getScore())
            );
        }

        return $list . '</table>';
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
            $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->assign('searchMode', $this->searchMode);

            /**
             * Check access rights
             */
            \isys_auth_search::instance()
                ->check(\isys_auth::EMPTY_ID_PARAM, "search");

            /**
             * Set paths to templates
             */
            $this->paths['contentbottomcontent'] = $p_module->get_template_dir() . 'main.tpl';
            $this->paths['contenttop']           = false;

            $p_template->assign("objectTableList", $this->getHtmlList($this->searchString))
                ->assign(
                    'headline',
                    _L(
                        'LC__MODULE__SEARCH__RESULT_HEADLINE',
                        [
                            $this->searchString,
                            count($this->data->getResult())
                        ]
                    )
                );

            if (count($this->data->getResult()) > 0)
            {
                if (count($this->data->getResult()) === \isys_tenantsettings::get('search.limit', 2500))
                {
                    \isys_application::instance()->container['notify']->warning(
                        _L('LC__MODULE__SEARCH__LIMIT_INFO', [\isys_tenantsettings::get('search.limit', 2500)])
                    );
                }

                return $this;
            }

            else
            {
                $p_template->assign("error", _L('LC__MODULE__SEARCH__NO_RESULTS', [$this->searchString]));
            }

        }
        catch (\isys_exception_auth $e)
        {
            $p_template->assign("exception", $e->write_log());
            $p_template->include_template('contentbottomcontent', 'exception-auth.tpl');
        }
        catch (\Exception $e)
        {
            \isys_application::instance()->container['notify']->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $p_row
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