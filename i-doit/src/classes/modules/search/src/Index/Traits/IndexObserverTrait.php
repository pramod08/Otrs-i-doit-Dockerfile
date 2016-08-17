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
namespace idoit\Module\Search\Index\Traits;

use idoit\Module\Search\Index\Protocol\Document;
use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Protocol\Observer;

/**
 * i-doit
 *
 * Search index manager
 *
 * Uses observable pattern to inform several handles about the indexed ci's.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
trait IndexObserverTrait
{
    /**
     * @var Observer[]
     */
    private $observer = [];

    /**
     * Amount of Cis to be indexed
     *
     * @var int
     */
    private $documentsToBeIndexed = 0;

    /**
     * Notify all observers
     *
     * @param Document $document
     */
    public function notify(Document $document, ObservableIndexManager $indexManager)
    {
        foreach ($this->observer as $observerItem)
        {
            $observerItem->update($document, $indexManager);
        }
    }

    /**
     * @return Observer[]
     */
    public function getObserver()
    {
        return $this->observer;
    }

    /**
     * @return int
     */
    public function getDocumentsToBeIndexed()
    {
        return $this->documentsToBeIndexed;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function attach(Observer $observer)
    {
        $this->observer[] = $observer;

        return $this;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function detach(Observer $observer)
    {
        foreach ($this->observer as $key => $observerItem)
        {
            if ($observerItem === $observer)
            {
                unset($this->observer[$key]);
            }
        }

        return $this;
    }
}