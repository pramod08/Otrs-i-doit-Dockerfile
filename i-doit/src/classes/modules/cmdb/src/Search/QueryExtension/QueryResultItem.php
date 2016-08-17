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
namespace idoit\Module\Cmdb\Search\QueryExtension;

use idoit\Module\Cmdb\Model\CiType;
use idoit\Module\Cmdb\Model\CiTypeCache;
use idoit\Module\Search\Query\AbstractQueryResultItem;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResultItem as QueryResultItemProtocol;

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
class QueryResultItem extends AbstractQueryResultItem implements QueryResultItemProtocol, \JsonSerializable
{
    /**
     * @var string
     */
    protected $type = 'cmdb';

    /**
     * @var array
     */
    protected $keys;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var CiType[]
     */
    private static $ciTypeCache = null;

    /**
     * @return string
     */
    public function getKey()
    {
        if (!self::$ciTypeCache)
        {
            self::$ciTypeCache = CiTypeCache::instance(\isys_application::instance()->database)
                ->getCiTypes();
        }

        return ucwords(
            (is_numeric($this->keys[0]) && isset(self::$ciTypeCache[$this->keys[0]]) ? self::$ciTypeCache[$this->keys[0]] . ' > ' : '') . str_replace('_', ' ', $this->keys[1])
        ) . ' > ' . str_replace(
            '_',
            ' ',
            ucwords($this->keys[3])
        );
    }

    /**
     * @return string
     */
    public function getLink()
    {
        $categoryAddition = '';
        $class            = 'isys_cmdb_dao_category_g_' . $this->keys[1];

        if (!class_exists($class))
        {
            $class = 'isys_cmdb_dao_category_s_' . $this->keys[1];
            if (!class_exists($class))
            {
                $class = false;
            }
        }
        if ($class)
        {
            /**
             * @var $instance \isys_cmdb_dao_category
             */
            $instance         = call_user_func(
                [
                    $class,
                    'instance'
                ],
                \isys_application::instance()->database
            );
            $categoryAddition = '&' . $instance->get_category_type_abbr() . 'ID=' . $instance->get_category_id() . '&cateID=' . $this->categoryId;

            if ($instance instanceof \isys_cmdb_dao_category_g_custom_fields && method_exists($instance, 'get_catg_custom_id'))
            {
                if (is_numeric($this->keys[4]))
                {
                    $categoryAddition .= '&customID=' . $this->keys[4];
                }
                else
                {
                    // Fallback: just link to the object.
                    $categoryAddition = '';
                }
            }
        }

        if (!$categoryAddition)
        {
            if (defined('C__CATG__' . strtoupper($this->keys[1])))
            {
                if ($this->keys[1] == 'custom_fields')
                {
                    $categoryAddition = '&catgID=' . constant('C__CATG__' . strtoupper($this->keys[1])) . '&cateID=' . $this->categoryId;
                }
                else
                {
                    $categoryAddition = '&catgID=' . constant('C__CATG__' . strtoupper($this->keys[1])) . '&cateID=' . $this->categoryId;
                }
            }
            elseif (defined('C__CATS__' . strtoupper($this->keys[1])))
            {
                $categoryAddition = '&catsID=' . constant('C__CATS__' . strtoupper($this->keys[1])) . '&cateID=' . $this->categoryId;
            }
            elseif ($this->keys[1] === 'network_port')
            {
                $categoryAddition = '&catgID=' . C__CMDB__SUBCAT__NETWORK_PORT . '&cateID=' . $this->categoryId;
            }
            elseif ($this->keys[1] === 'storage_device')
            {
                $categoryAddition = '&catgID=' . C__CMDB__SUBCAT__STORAGE__DEVICE . '&cateID=' . $this->categoryId;
            }
            else $categoryAddition = '';
        }

        if (isset($this->conditions[0]))
        {
            $categoryAddition .= '&highlight=' . urlencode($this->conditions[0]);
        }

        return rtrim(
            \isys_application::instance()->www_path,
            '/'
        ) . '/?objID=' . $this->getDocumentId() . $categoryAddition;
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
            'link'       => $this->getLink(),
            'score'      => $this->getScore()
        ];
    }

    /**
     * QueryResultItem constructor.
     *
     * @param int         $documentId
     * @param string      $key
     * @param string      $value
     * @param double      $score
     * @param Condition[] $conditions
     */
    public function __construct($documentId, $key, $value, $score, array $conditions)
    {
        parent::__construct($documentId, $key, $value, $score, $conditions);

        $this->keys       = explode('.', $this->key);
        $this->categoryId = $this->keys[2];
    }

}