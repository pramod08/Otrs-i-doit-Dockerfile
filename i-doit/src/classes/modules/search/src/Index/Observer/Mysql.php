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
namespace idoit\Module\Search\Index\Observer;

use idoit\Component\ContainerFacade;
use idoit\Model\Dao\Base;
use idoit\Module\Cmdb\Model\Ci;
use idoit\Module\Search\Index\Protocol\Document;
use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Protocol\Observer;
use isys_component_database as Database;

/**
 * i-doit
 *
 * Mysql Observer
 *
 * Retrieves all indexed cis and writes them into the mysql table isys_search_idx
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Mysql extends AbstractObserver implements Observer
{
    /**
     * Dao component
     *
     * @var Base
     */
    private $dao;

    /**
     * @param Document               $document
     * @param ObservableIndexManager $indexManager
     */
    public function update(Document $document, ObservableIndexManager $indexManager)
    {
        foreach ($document->getData() as $key => $value)
        {
            if ($value === '-') continue;
            if (!$value) continue;

            try
            {
                if (!$this->dao->update(
                    'REPLACE INTO isys_search_idx ' . 'SET ' . 'isys_search_idx__version = 1, ' . 'isys_search_idx__type = \'' . $document->getType() . '\', ' .
                    'isys_search_idx__reference = ' . $this->dao->convert_sql_id(
                        $document->getId()
                    ) . ', ' . 'isys_search_idx__key = ' . $this->dao->convert_sql_text($key) . ', ' . 'isys_search_idx__value = ' . $this->dao->convert_sql_text(
                        $value
                    ) . ' ' . ';'
                )
                )
                {
                    throw new \Exception('Error creating search index for key "' . $key . '"');
                }
            }
            catch (\Exception $e)
            {
                $this->getDi()->logger->error($e->getMessage(), ['scope' => 'SearchIndexCreation']);
            }
        }

        unset($flatCi);
    }

    /**
     * CmdbObject constructor.
     *
     * @param Database $database
     */
    public function __construct(ContainerFacade $container, $clear = true)
    {
        parent::__construct($container);

        $this->dao = new Base($container->database);
        $this->dao->get_database_component()
            ->begin();

        if ($clear)
        {
            $this->dao->update('DELETE FROM isys_search_idx;');
        }

        $that = $this;
        register_shutdown_function(
            function () use ($that)
            {
                $that->dao->get_database_component()
                    ->commit();
            }
        );
    }
}