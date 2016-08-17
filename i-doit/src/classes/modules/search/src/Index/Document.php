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
namespace idoit\Module\Search\Index;

use idoit\Component\Provider\Factory;
use idoit\Module\Search\Index\Protocol\Document as DocumentProtocol;

/**
 * i-doit
 *
 * Search index document
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Document implements DocumentProtocol
{
    use Factory;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Documents id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Documents title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set documents data.
     *
     * Plain array structure:
     *  ['cpu.1.frequency' => 2127, 'cpu.1.title' => 'Xeon E7430']
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Module identifier
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

}