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

/**
 * i-doit
 *
 * Module Dao
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
abstract class isys_module_dao extends isys_component_dao
{

    /**
     * Property provides nothing.
     */
    const C__PROPERTY__PROVIDES__NONE = 0;

    /**
     * Property may be viewed.
     */
    const C__PROPERTY__PROVIDES__VIEW = 1;

    /**
     * Property may be created.
     */
    const C__PROPERTY__PROVIDES__CREATE = 2;

    /**
     * Property may be saved.
     */
    const C__PROPERTY__PROVIDES__SAVE = 4;

    /**
     * Property may be deleted.
     */
    const C__PROPERTY__PROVIDES__DELETE = 8;

    /**
     * Property is "virtual" = no db field.
     */
    const C__PROPERTY__PROVIDES__VIRTUAL = 16;

    /**
     * Validation says, property is fine.
     */
    const C__VALIDATION_RESULT__NOTHING = 0;

    /**
     * Validation ignored property.
     */
    const C__VALIDATION_RESULT__IGNORED = 1;

    /**
     * Validation says, property is missing.
     */
    const C__VALIDATION_RESULT__MISSING = 2;

    /**
     * Validation says, property is invalid.
     */
    const C__VALIDATION_RESULT__INVALID = 4;

    /**
     * Save mode: Insert new entity.
     */
    const C__MODE__INSERT = 1;

    /**
     * Save mode: Update existing entity.
     */
    const C__MODE__UPDATE = 2;
    /**
     * Property groups
     *
     * @var array Associative array of strings
     */
    protected $m_groups;
    /**
     * Information about properties
     *
     * @var array Associative array with property type as key and its properties
     * as value
     */
    protected $m_properties;
    /**
     * Data tables for properties
     *
     * @var array Associative array of strings
     */
    protected $m_tables;
    /**
     * Information about property types.
     *
     * @var array Associative array
     */
    protected $m_types;

    /**
     * get_data always retrieves the data of the main table of this module
     */
    abstract public function get_data(); //function

    /**
     * Gets information about properties.
     *
     * @param string $p_property_type (optional) Select property type. Defaults
     *                                to null (all properties will be fetched).
     *
     * @return array Associative array
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_properties($p_property_type = null)
    {
        if (!isset($this->m_properties))
        {
            $this->build_properties();
        } //if

        if (isset($p_property_type))
        {
            assert('array_key_exists($p_property_type, $this->m_properties)');

            return $this->m_properties[$p_property_type];
        } //if

        return $this->m_properties;
    } //function

    /**
     * Fetches entity data from database.
     *
     * @param string $p_property_type Select property type.
     * @param array  $p_selections    (optional) Select only these properties. If
     *                                not set (default), all properties will be selected.
     * @param array  $p_conditions    (optional) Make some conditions. Associative
     *                                array of properties as keys and the destinated values as values. Defaults
     *                                to no condition.
     * @param bool   $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                                false.
     * @param bool   $p_as_result_set (optional) Returns fetched data as result
     *                                set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_entities($p_property_type, $p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        assert('is_string($p_property_type)');
        assert('is_bool($p_raw)');
        assert('is_bool($p_as_result_set)');

        $l_properties = $this->get_properties($p_property_type);

        $l_selection = '*';

        if (isset($p_selections))
        {
            assert('is_array($p_selections)');

            $l_selected_properties = [];

            foreach ($p_selections as $l_property)
            {
                assert('array_key_exists($l_property, $l_properties)');

                $l_selected_properties[] = $l_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            } //foreach

            $l_selection = implode(', ', $l_selected_properties);
        } //if

        $l_condition = '';

        if (isset($p_conditions))
        {
            assert('is_array($p_conditions)');
            $l_condition = $this->build_condition($l_properties, $p_conditions);
        } //if

        $l_query = 'SELECT ' . $l_selection . ' FROM `' . $this->m_tables[$p_property_type] . '`' . $l_condition . ';';

        $l_result_set = $this->retrieve($l_query);

        if ($p_as_result_set)
        {
            return $l_result_set;
        } //if

        $l_result = $l_result_set->__as_array();

        if ($p_raw)
        {
            return $l_result;
        } //if

        $l_formatted_result = [];

        foreach ($l_result as $l_entity)
        {
            $l_id                      = $l_entity[$l_properties['id'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
            $l_formatted_result[$l_id] = $this->map_properties($p_property_type, $l_entity);
        } //foreach

        return $l_formatted_result;
    } //function

    /**
     * Saves an entity to database. If identifier is given in data array an
     * existing entity will be updated, if not a new entity will be created.
     *
     * @param string $p_property_type Select property type.
     * @param array  $p_data          Data which will be saved
     *
     * @return int Entity identifier
     */
    public function save($p_property_type, $p_data)
    {
        assert('is_string($p_property_type)');
        assert('is_array($p_data)');

        if (!array_key_exists($p_property_type, $this->m_tables))
        {
            throw new isys_exception_general(
                sprintf(
                    'Failed to save entity because of an unknown property type "s"',
                    $p_property_type
                )
            );
        } //if

        $l_properties = $this->get_properties($p_property_type);

        $l_fields        = [];
        $l_update_entity = null;

        $l_mode = self::C__MODE__INSERT;

        foreach ($l_properties as $l_property_id => $l_property_info)
        {
            // Identifier needs special handling:
            if ($l_property_id === 'id')
            {
                if (array_key_exists($l_property_id, $p_data))
                {
                    $l_mode          = self::C__MODE__UPDATE;
                    $l_update_entity = ' WHERE `' . $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` = ' . $this->convert_sql_id($p_data[$l_property_id]);
                } //if

                continue;
            } //if

            $l_value = null;

            if (!isset($p_data[$l_property_id]))
            {
                if (array_key_exists('default', $l_property_info[C__PROPERTY__DATA]))
                {
                    $l_value = $l_property_info[C__PROPERTY__DATA]['default'];
                } //if
            }
            else
            {
                $l_value = $p_data[$l_property_id];
            } //if

            if (!isset($l_value) && $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] === true)
            {
                throw new isys_exception_general(
                    sprintf(
                        'Failed to save entity because of missing property "%s" for entity type "%s"',
                        $l_property_id,
                        $p_property_type
                    )
                );
            } //if

            $l_field = '`' . $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` = ';

            if ($l_property_info[C__PROPERTY__DATA]['crypt'])
            {
                $l_value = isys_helper_crypt::encrypt($l_value);
            }

            switch ($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
            {
                case 'varchar':
                case 'text':
                    $l_field .= $this->convert_sql_text($l_value);
                    break;
                case 'int':
                    if (is_numeric($l_value))
                    {
                        if (isset($l_property_info[C__PROPERTY__DATA]['params']) && (in_array('primary_key', $l_property_info[C__PROPERTY__DATA]['params']) || in_array(
                                    'unsigned',
                                    $l_property_info[C__PROPERTY__DATA]['params']
                                ))
                        )
                        {
                            $l_field .= $this->convert_sql_id($l_value);
                        }
                        else
                        {
                            $l_field .= $this->convert_sql_int($l_value);
                        } //if
                    }
                    else
                    {
                        unset($l_field);
                    }
                    break;
                case 'float':
                    $l_field .= $this->convert_sql_float($l_value);
                    break;
                case 'datetime':
                    $l_field .= $this->convert_sql_datetime($l_value);
                    break;
                default:
                    throw new isys_exception_general(
                        sprintf(
                            'Failed to save entity because of an unknown data type "%s" within property type "%s"',
                            $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE],
                            $p_property_type
                        )
                    );
                    break;
            } //switch

            if (isset($l_field)) $l_fields[] = $l_field;
        } //foreach

        $l_query = null;

        switch ($l_mode)
        {
            case self::C__MODE__INSERT:
                $l_query = 'INSERT INTO';
                break;
            case self::C__MODE__UPDATE:
                $l_query = 'UPDATE';
                break;
        } //switch

        $l_query .= ' ' . $this->m_tables[$p_property_type] . ' SET ' . implode(', ', $l_fields);

        if ($l_mode === self::C__MODE__UPDATE)
        {
            $l_query .= $l_update_entity;
        } //if

        $l_query .= ';';
        if (!$this->update($l_query))
        {
            throw new isys_exception_general('Unexpected database error occured.');
        } //if

        $l_id = null;

        switch ($l_mode)
        {
            case self::C__MODE__INSERT:
                $l_id = intval($this->get_last_insert_id());
                break;
            case self::C__MODE__UPDATE:
                $l_id = $p_data['id'];
                break;
        } //switch

        return $l_id;
    }

    /**
     * Deletes an existing entity from database.
     *
     * @param string $p_property_type Select property type.
     * @param array  $p_conditions    (optional) Conditions. Defaults to null.
     */
    public function delete($p_property_type, $p_conditions = null)
    {
        assert('is_string($p_property_type)');

        $l_types = $this->get_property_types();

        if (!array_key_exists($p_property_type, $l_types))
        {
            throw new isys_exception_general(
                sprintf(
                    'Failed to delete one or more entities because of unknown property type "%s"',
                    $p_property_type
                )
            );
        } //if

        $l_condition = '';

        if (isset($p_conditions))
        {
            assert('is_array($p_conditions)');
            $l_properties = $this->get_properties($p_property_type);
            $l_condition  = $this->build_condition($l_properties, $p_conditions);
        } //if

        $l_query = 'DELETE FROM `' . $this->m_tables[$p_property_type] . '`' . $l_condition . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                sprintf(
                    'Failed to delete one or more entities for property type "%s" because an unexpected database error occured.',
                    $p_property_type
                )
            );
        } //if
    } //function

    /**
     * Prettfies properties.
     *
     * @param string $p_property_type Select property type.
     * @param arary  $p_properties    Properties fetched from database
     *
     * @return array Associative array
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    protected function map_properties($p_property_type, $p_properties)
    {
        $l_properties = $this->get_properties($p_property_type);

        $l_map = [];

        foreach ($l_properties as $l_tag => $l_property)
        {
            if (array_key_exists($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], $p_properties))
            {
                $l_value = $p_properties[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];

                if ($l_value !== null && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]))
                {
                    switch ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
                    {
                        case 'int':
                            $l_value = intval($l_value);
                            break;
                        case 'float':
                            $l_value = floatval($l_value);
                            break;
                    } //switch
                } //if
                if ($l_property[C__PROPERTY__DATA]['crypt'])
                {
                    $l_value = isys_helper_crypt::decrypt($l_value);
                }
                $l_map[$l_tag] = $l_value;
            } //if
        } //foreach

        return $l_map;
    } //function

    /**
     * @param $p_properties
     * @param $p_conditions
     *
     * @return string
     */
    protected function build_condition($p_properties, $p_conditions)
    {
        $l_properties_for_condition = [];

        foreach ($p_conditions as $l_property => $l_destinated_value)
        {
            assert('array_key_exists($l_property, $p_properties)');

            if (is_array($l_destinated_value))
            {
                $l_condition = [];
                foreach ($l_destinated_value AS $l_value)
                {
                    if (strtolower($l_value) == 'null')
                    {
                        $l_condition[] = '`' . $p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` IS NULL ';
                    }
                    else
                    {
                        switch ($p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
                        {
                            case 'int':
                                if (isset($p_properties[$l_property][C__PROPERTY__DATA]['params']) && in_array(
                                        'unsigned',
                                        $p_properties[$l_property][C__PROPERTY__DATA]['params']
                                    )
                                )
                                {
                                    $l_value = $this->convert_sql_id($l_value);
                                }
                                else
                                {
                                    $l_value = $this->convert_sql_int($l_value);
                                } //if
                                break;
                            case 'varchar':
                            case 'text':
                                $l_value = $this->convert_sql_text($l_value);
                                break;
                            case 'float':
                                $l_value = $this->convert_sql_float($l_value);
                                break;
                            case 'datetime':
                                $l_value = $this->convert_sql_datetime($l_value);
                                break;
                            case 'boolean':
                                $l_value = $this->convert_sql_boolean($l_value);
                                break;
                        } //switch
                        $l_condition[] = '`' . $p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` = ' . $l_value;
                    } // if
                } // foreach
                $l_properties_for_condition[] = '(' . implode(' OR ', $l_condition) . ')';
            }
            else
            {
                if (strtolower($l_destinated_value) == 'null')
                {
                    $l_properties_for_condition[] = '`' . $p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` IS NULL ';
                }
                else
                {
                    switch ($p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
                    {
                        case 'int':
                            if (isset($p_properties[$l_property][C__PROPERTY__DATA]['params']) && in_array(
                                    'unsigned',
                                    $p_properties[$l_property][C__PROPERTY__DATA]['params']
                                )
                            )
                            {
                                $l_destinated_value = $this->convert_sql_id($l_destinated_value);
                            }
                            else
                            {
                                $l_destinated_value = $this->convert_sql_int($l_destinated_value);
                            } //if
                            break;
                        case 'varchar':
                        case 'text':
                            $l_destinated_value = $this->convert_sql_text($l_destinated_value);
                            break;
                        case 'float':
                            $l_destinated_value = $this->convert_sql_float($l_destinated_value);
                            break;
                        case 'datetime':
                            $l_destinated_value = $this->convert_sql_datetime($l_destinated_value);
                            break;
                        case 'boolean':
                            $l_destinated_value = $this->convert_sql_boolean($l_destinated_value);
                            break;
                    } //switch

                    $l_properties_for_condition[] = '`' . $p_properties[$l_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` = ' . $l_destinated_value;
                } // if
            } // if
        } //foreach

        return ' WHERE ' . implode(' AND ', $l_properties_for_condition);
    }

} //class