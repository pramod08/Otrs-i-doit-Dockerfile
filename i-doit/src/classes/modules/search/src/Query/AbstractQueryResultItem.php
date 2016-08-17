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
namespace idoit\Module\Search\Query;

/**
 * i-doit
 *
 * Default query result item
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class AbstractQueryResultItem implements \JsonSerializable
{
    /**
     * @var Condition[]
     */
    protected $conditions = [];

    /**
     * @var int
     */
    protected $documentId;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var double
     */
    protected $score;

    /**
     * @var string
     */
    protected $type = 'search';

    /**
     * @var string
     */
    protected $value;

    /**
     * @inheritdoc
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return int
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * JsonSerializable Interface
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'documentId' => $this->getDocumentId(),
            'key'        => $this->getKey(),
            'value'      => $this->getValue(),
            'type'       => $this->getType(),
            'score'      => $this->getScore()
        ];
    }

    /**
     * QueryResultItem constructor.
     *
     * @param int    $documentId
     * @param string $key
     * @param string $value
     * @param double $score
     */
    public function __construct($documentId, $key, $value, $score, array $conditions)
    {
        $this->documentId = $documentId;
        $this->key        = $key;
        $this->value      = $value;
        $this->score      = $score;
        $this->conditions = $conditions;
    }

}