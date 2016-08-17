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
namespace idoit\Module\Search\Index\Protocol;

use idoit\Module\Cmdb\Model\Ci;

/**
 * i-doit
 *
 * Search index manager
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface ObservableIndexManager
{
    /**
     * Give the index manager a name (for UI purpose)
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Return the index managers name
     *
     * @return $this
     */
    public function getName();

    /**
     * Attach new observer
     *
     * @param Observer $observer
     *
     * @return mixed
     */
    public function attach(Observer $observer);

    /**
     * Index creation method
     *
     * @return mixed
     */
    public function create();

    /**
     * Detach existing observer
     *
     * @param Observer $observer
     *
     * @return mixed
     */
    public function detach(Observer $observer);

    /**
     * Return remaining documents, that are registered to be indexed
     *
     * @return int
     */
    public function getDocumentsToBeIndexed();

    /**
     * Get indexed documents so far
     *
     * @return int
     */
    public function getIndexedDocuments();

    /**
     * Get indexed properties so far
     *
     * @return int
     */
    public function getIndexedItems();

    /**
     * Notify all observers
     *
     * @param Document $document
     *
     * @return mixed
     */
    public function notify(Document $document, ObservableIndexManager $indexManager);

    /**
     * Remove index item(s) by reference id
     *
     * @return mixed
     */
    public function remove($referenceId);
}