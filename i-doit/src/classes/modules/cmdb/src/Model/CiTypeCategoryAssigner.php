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
namespace idoit\Module\Cmdb\Model;

use idoit\Component\Provider\Factory;
use idoit\Component\Provider\Singleton;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CiTypeCategoryAssigner
{
    use Singleton, Factory;

    /**
     * Boolean switch to process all CI types.
     *
     * @var  boolean
     */
    private $allCiTypes = false;
    /**
     * Array of categories to be assigned.
     *
     * @var  array
     */
    private $categories = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => null,
        C__CMDB__CATEGORY__TYPE_CUSTOM   => [],
    ];
    /**
     * Array of CI types to be checked for categories.
     *
     * @var  array
     */
    private $ciTypes = [];

    /**
     * Array of CI types to be excluded.
     * @var  array
     */
    private $ciTypesToExclude = [];

    /**
     * DAO instance.
     *
     * @var  \isys_cmdb_dao_object_type
     */
    private $dao = null;
    /**
     * Database instance.
     *
     * @var  \isys_component_database
     */
    private $database = null;

    /**
     * Method for setting all CI types.
     *
     * @param   boolean $activate
     *
     * @return  $this
     */
    public function setAllCiTypes($activate = true)
    {
        $this->ciTypes    = [];
        $this->allCiTypes = !!$activate;

        return $this;
    } // function

    /**
     * Set any desirec CI types by their string constants or IDs.
     *
     * @param   array  $ciTypes
     *
     * @return  $this
     */
    public function excludeCiTypes(array $ciTypes)
    {
        $this->ciTypesToExclude = $ciTypes;

        return $this;
    } // function

    /**
     * Set any desired CI types by their string constants or IDs.
     *
     * @param   array $ciTypes
     *
     * @return  $this
     */
    public function setCiTypes(array $ciTypes)
    {
        $this->ciTypes    = $ciTypes;
        $this->allCiTypes = false;

        return $this;
    } // function

    /**
     * Add a single CI type by its string constant or ID.
     *
     * @param   string $ciType
     *
     * @return  $this
     */
    public function addCiType($ciType)
    {
        $this->ciTypes[]  = $ciType;
        $this->allCiTypes = false;

        return $this;
    } // function

    /**
     * Set any desired global categories by their ID or string constant.
     *
     * @param   array $categories
     *
     * @return  $this
     */
    public function setGlobalCategories(array $categories)
    {
        $this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = $categories;

        return $this;
    } // function

    /**
     * Set a single specific category by its ID or string constant.
     *
     * @param   mixed $category
     *
     * @return  $this
     */
    public function setSpecificCategory($category)
    {
        if (is_numeric($category) || is_string($category))
        {
            $this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $category;
        } // if

        return $this;
    } // function

    /**
     * Set any desired custom categories by their ID or string constant.
     *
     * @param   array $categories
     *
     * @return  $this
     */
    public function setCustomCategories(array $categories)
    {
        $this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = $categories;

        return $this;
    } // function

    /**
     * This method will start assigning the defined categories to the defined CI types.
     *
     * @return  $this
     */
    public function assign()
    {
        // Exit, if no categories have been selected.
        if (!count($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL]) && !count(
                $this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM]
            ) && !$this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]
        )
        {
            return $this;
        } // if

        // Check for CI types.
        if ($this->allCiTypes)
        {
            // We'd like to process all CI types.
            $result = $this->dao->get_object_types();
        }
        else if (count($this->ciTypes))
        {
            $this->ciTypes = array_unique($this->ciTypes);

            // We'd like to process only specific CI types.
            $result = $this->dao->get_object_types($this->ciTypes);
        }
        else
        {
            // No CI types have been selected: exit!
            return $this;
        } // if

        // Iterate over the results.
        if (count($result))
        {
            // First we'll retrieve a clean array of category IDs.
            $categories = $this->collectCategoryIDs();
            $ciTypes    = [];
            $ciTypeBlacklist = array_flip($this->ciTypesToExclude);

            while ($ciType = $result->get_row())
            {
                if (isset($ciTypeBlacklist[$ciType['isys_obj_type__id']]) || isset($ciTypeBlacklist[$ciType['isys_obj_type__const']]))
                {
                    continue;
                } // if

                $ciTypes[] = $this->dao->convert_sql_id($ciType['isys_obj_type__id']);
            } // while

            $ciTypes = array_unique(array_filter($ciTypes));

            $this->dao->begin_update();

            if (count($ciTypes))
            {
                if (count($categories[C__CMDB__CATEGORY__TYPE_GLOBAL]))
                {
                    $this->assignGlobalCategories($categories[C__CMDB__CATEGORY__TYPE_GLOBAL], $ciTypes);
                } // if

                if ($categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] > 0)
                {
                    $this->assignSpecificCategories($categories[C__CMDB__CATEGORY__TYPE_SPECIFIC], $ciTypes);
                } // if

                if (count($categories[C__CMDB__CATEGORY__TYPE_CUSTOM]))
                {
                    $this->assignCustomCategories($categories[C__CMDB__CATEGORY__TYPE_CUSTOM], $ciTypes);
                } // if
            } // if

            // Remove all duplicate global- and custom category assignments.
            $this->deleteDuplicateAssignments();

            // At the end, apply the update.
            $this->dao->apply_update();
        } // if

        return $this;
    } // function

    /**
     * This method will remove all duplicated global- and custom category assignments.
     *
     * @return  $this
     * @throws  \isys_exception_dao
     */
    public function deleteDuplicateAssignments()
    {
        // This query will remove all duplicated assignments of global categories.
        $deleteDuplicateQuery = "DELETE main FROM isys_obj_type_2_isysgui_catg main
            LEFT JOIN (
                SELECT *, COUNT(*) c
                FROM isys_obj_type_2_isysgui_catg
                GROUP BY isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id
                HAVING c > 1) sub
                    ON main.isys_obj_type_2_isysgui_catg__isysgui_catg__id = sub.isys_obj_type_2_isysgui_catg__isysgui_catg__id
                    AND main.isys_obj_type_2_isysgui_catg__isys_obj_type__id = sub.isys_obj_type_2_isysgui_catg__isys_obj_type__id
            WHERE main.isys_obj_type_2_isysgui_catg__id != sub.isys_obj_type_2_isysgui_catg__id;";

        $this->dao->update($deleteDuplicateQuery);

        $deleteDuplicateQuery = "DELETE main FROM isys_obj_type_2_isysgui_catg_custom main
            LEFT JOIN (
                SELECT *, COUNT(*) c
                FROM isys_obj_type_2_isysgui_catg_custom
                GROUP BY isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id, isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
                HAVING c > 1) sub
                    ON main.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = sub.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
                    AND main.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = sub.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id
            WHERE main.isys_obj_type_2_isysgui_catg_custom__id != sub.isys_obj_type_2_isysgui_catg_custom__id;";

        $this->dao->update($deleteDuplicateQuery);

        return $this;
    } // function

    /**
     * This method will be used to retrieve uniform category IDs by given constants and IDs mixed together.
     *
     * @return  array
     */
    protected function collectCategoryIDs()
    {
        $categories = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => null,
            C__CMDB__CATEGORY__TYPE_CUSTOM   => []
        ];

        // Go sure to include every category only once.
        $this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = array_unique($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL]);
        $this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = array_unique($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM]);

        foreach ($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $globalCategory)
        {
            if (is_numeric($globalCategory))
            {
                $categories[C__CMDB__CATEGORY__TYPE_GLOBAL][] = $this->dao->convert_sql_id($globalCategory);
            }
            else
            {
                $globalCategoryID = $this->dao->get_catg_by_const($globalCategory)
                    ->get_row_value('isysgui_catg__id');

                if ($globalCategoryID > 0)
                {
                    $categories[C__CMDB__CATEGORY__TYPE_GLOBAL][] = $this->dao->convert_sql_id($globalCategoryID);
                } // if
            } // if
        } // foreach

        if (!empty($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]))
        {
            if (is_numeric($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]))
            {
                $categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $this->dao->convert_sql_id($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]);
            }
            else
            {
                $specificCategoryID = $this->dao->get_cats_by_const($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])
                    ->get_row_value('isysgui_cats__id');

                if ($specificCategoryID > 0)
                {
                    $categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $this->dao->convert_sql_id($specificCategoryID);
                } // if
            } // if
        } // if

        foreach ($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] as $customCategory)
        {
            if (is_numeric($customCategory))
            {
                $categories[C__CMDB__CATEGORY__TYPE_CUSTOM][] = $this->dao->convert_sql_id($customCategory);
            }
            else
            {
                $customCategoryID = $this->dao->get_catc_by_const($customCategory)
                    ->get_row_value('isysgui_catg_custom__id');

                if ($customCategoryID > 0)
                {
                    $categories[C__CMDB__CATEGORY__TYPE_CUSTOM][] = $this->dao->convert_sql_id($customCategoryID);
                } // if
            } // if
        } // foreach

        return $categories;
    } // function

    /**
     * Method for assigning global categories.
     *
     * @param   array $categories
     * @param   array $ciTypes
     *
     * @return  string
     */
    protected function assignGlobalCategories(array $categories, array $ciTypes)
    {
        $values = [];

        foreach ($ciTypes as $ciType)
        {
            foreach ($categories as $category)
            {
                $values[] = '(' . $ciType . ', ' . $category . ')';
            } // foreach
        } // foreach

        if (count($values))
        {
            $sql = 'INSERT INTO isys_obj_type_2_isysgui_catg
                (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id)
                VALUES ' . implode(',', $values) . ';';

            return $this->dao->update($sql);
        } // if

        return true;
    } // function

    /**
     * Method for assigning specific categories.
     *
     * @param   integer $category
     * @param   array   $ciTypes
     *
     * @return  string
     */
    protected function assignSpecificCategories($category, array $ciTypes)
    {
        $condition = '';

        if (!$this->allCiTypes)
        {
            $condition = ' WHERE isys_obj_type__id ' . $this->dao->prepare_in_condition($ciTypes);;
        } // if

        if ($category > 0)
        {
            $sql = 'UPDATE isys_obj_type
                SET isys_obj_type__isysgui_cats__id = ' . $this->dao->convert_sql_id($category) . $condition . ';';

            return $this->dao->update($sql);
        } // if

        return true;
    } // function

    /**
     * Method for assigning custom categories.
     *
     * @param   array $categories
     * @param   array $ciTypes
     *
     * @return  string
     */
    protected function assignCustomCategories(array $categories, array $ciTypes)
    {
        $values = [];

        foreach ($ciTypes as $ciType)
        {
            foreach ($categories as $category)
            {
                $values[] = '(' . $ciType . ', ' . $category . ')';
            } // foreach
        } // foreach

        if (count($values))
        {
            $sql = 'INSERT INTO isys_obj_type_2_isysgui_catg_custom
                (isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id, isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id)
                VALUES ' . implode(',', $values) . ';';

            return $this->dao->update($sql);
        } // if

        return true;
    } // function

    /**
     * Simple CiTypeCategoryAssigner constructor.
     *
     * @param  \isys_component_database $database
     */
    public function __construct(\isys_component_database $database)
    {
        $this->database = $database;
        $this->dao      = \isys_cmdb_dao_object_type::instance($this->database);
    } // function
} // class