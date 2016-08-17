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
use idoit\Module\Cmdb\Model\Ci;
use idoit\Module\Search\Index\Protocol\Document;
use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Protocol\Observer;
use isys_application as Application;

/**
 * i-doit
 *
 * Json Observer
 *
 * Retrieves all indexed cis and writes them into a json file
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class JsonDump extends AbstractObserver implements Observer
{
    /**
     * The filename the json is written to
     *
     * @var string
     */
    private $filename;

    /**
     * The index
     *
     * @var array
     */
    private $index = [];

    /**
     * @param Document               $document
     * @param ObservableIndexManager $indexManager
     */
    public function update(Document $document, ObservableIndexManager $indexManager)
    {
        $this->index[] = $document;
    }

    /**
     * Json constructor.
     *
     * @param string $filename
     */
    public function __construct(ContainerFacade $container, $filename = 'temp/objects.json')
    {
        if ($filename[0] !== '/')
        {
            $filename = Application::instance()->app_path . '/' . $filename;
        }

        $this->filename = $filename;

        // Passing container
        parent::__construct($container);
    }

    /**
     * Write file on destruction
     */
    public function __destruct()
    {
        file_put_contents($this->filename, json_encode($this->index));
    }

}