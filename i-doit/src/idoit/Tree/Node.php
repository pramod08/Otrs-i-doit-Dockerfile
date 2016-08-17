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
namespace idoit\Tree;

/**
 * i-doit Tree Node Wrapper
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

class Node extends \isys_tree_node
{
    /**
     * @var int
     */
    private static $idCounter = 0;
    /**
     * @var bool
     */
    public $accessRight = true;
    /**
     * @var string
     */
    public $cssClass = '';
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var string
     */
    public $image = '';
    /**
     * @var string
     */
    public $link;
    /**
     * @var string
     */
    public $onclick = '';
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $tooltip = '';
    /**
     * Parent node
     *
     * @var Node
     */
    protected $m_parent;

    /**
     * Factory method for chaining
     *
     * @param        $p_title
     * @param        $p_link
     * @param string $p_image
     * @param string $p_onclick
     * @param string $p_tooltip
     * @param string $p_cssClass
     * @param bool   $p_accessRight
     */
    public static function factory($p_title, $p_link, $p_image = '', $p_onclick = '', $p_tooltip = '', $p_cssClass = '', $p_accessRight = true)
    {
        return new self($p_title, $p_link, $p_image, $p_onclick, $p_tooltip, $p_cssClass, $p_accessRight);
    }

    /**
     * @return Node
     */
    public function get_parent()
    {
        return $this->m_parent;
    }

    /**
     * @param string $p_title
     * @param string $p_link
     * @param string $p_image
     * @param string $p_onclick
     */
    public function __construct($p_title, $p_link, $p_image = '', $p_onclick = '', $p_tooltip = '', $p_cssClass = '', $p_accessRight = true)
    {
        $this->id = self::$idCounter++;

        $this->title       = $p_title;
        $this->link        = $p_link;
        $this->accessRight = $p_accessRight;

        if ($p_image)
        {
            $this->image = $p_image;
        }

        if ($p_onclick)
        {
            $this->onclick = $p_onclick;
        }

        if ($p_tooltip)
        {
            $this->tooltip = $p_tooltip;
        }

        if ($p_cssClass)
        {
            $this->cssClass = $p_cssClass;
        }

        parent::__construct([]);
    }

}