<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\Column;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ColumnFactory
{
    /**
     * @param string $pName
     * @param boolean $pDefault
     * @return Column
     */
    public static function boolean($pName, $pDefault = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::TINYINT,
                'value' => 1,
                'attribute' => Column::UNSIGNED,
                'default' => $pDefault ? 1 : 0,
                'autoIncrement' => false
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pAttribute
     * @param boolean $pAutoIncrement
     * @return Column
     */
    public static function tinyInt($pName, 
                                   $pLength = 4, 
                                   $pAttribute = Column::UNSIGNED, 
                                   $pAutoIncrement = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::TINYINT,
                'value' => $pLength,
                'attribute' => $pAttribute,
                'autoIncrement' => $pAutoIncrement
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pAttribute
     * @param boolean $pAutoIncrement
     * @return Column
     */
    public static function smallInt($pName, 
                                    $pLength = 6, 
                                    $pAttribute = Column::UNSIGNED, 
                                    $pAutoIncrement = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::SMALLINT,
                'value' => $pLength,
                'attribute' => $pAttribute,
                'autoIncrement' => $pAutoIncrement
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pAttribute
     * @param boolean $pAutoIncrement
     * @return Column
     */
    public static function mediumInt($pName, 
                                     $pLength = 9, 
                                     $pAttribute = Column::UNSIGNED, 
                                     $pAutoIncrement = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::MEDIUMINT,
                'value' => $pLength,
                'attribute' => $pAttribute,
                'autoIncrement' => $pAutoIncrement
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pAttribute
     * @param boolean $pAutoIncrement
     * @return Column
     */
    public static function int($pName, 
                               $pLength = 11, 
                               $pAttribute = Column::UNSIGNED, 
                               $pAutoIncrement = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::INT,
                'value' => $pLength,
                'attribute' => $pAttribute,
                'autoIncrement' => $pAutoIncrement
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pAttribute
     * @param boolean $pAutoIncrement
     * @return Column
     */
    public static function bigInt($pName, 
                                  $pLength = 20, 
                                  $pAttribute = Column::UNSIGNED, 
                                  $pAutoIncrement = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::BIGINT,
                'value' => $pLength,
                'attribute' => $pAttribute,
                'autoIncrement' => $pAutoIncrement
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param integer $pDecimal
     * @param boolean $pNullable
     * @return Column
     */
    public static function float($pName, 
                                 $pLength = 8, 
                                 $pDecimal = 2,
                                 $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::FLOAT,
                'value' => [$pLength, $pDecimal],
                'nullable' => $pNullable
            ]
        );
    }
    
   /**
     * @param string $pName
     * @param integer $pLength
     * @param integer $pDecimal
     * @param boolean $pNullable
     * @return Column
     */
    public static function double($pName, 
                                  $pLength = 8, 
                                  $pDecimal = 2,
                                  $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::DOUBLE,
                'value' => [$pLength, $pDecimal],
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @return Column
     */
    public static function date($pName, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::DATE,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param boolean $pNullable
     * @return Column
     */
    public static function dateTime($pName, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::DATETIME,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param string $pDefault
     * @param string $pAttribute
     * @param boolean $pNullable
     * @return Column
     */
    public static function timestamp($pName, 
                                     $pDefault = Column::CURRENT_TIMESTAMP, 
                                     $pAttribute = Column::UPDATE_CURRENT_TIMESTAMP, 
                                     $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::TIMESTAMP,
                'default' => $pDefault,
                'attribute' => $pAttribute,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pNullable
     * @return Column
     */
    public static function char($pName, $pLength = 255, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::CHAR,
                'value' => $pLength,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param integer $pLength
     * @param boolean $pNullable
     * @return Column
     */
    public static function varchar($pName, $pLength = 255, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::VARCHAR,
                'value' => $pLength,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param boolean $pNullable
     * @return Column
     */
    public static function text($pName, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::TEXT,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param boolean $pNullable
     * @return Column
     */
    public static function binary($pName, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::BINARY,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param boolean $pNullable
     * @return Column
     */
    public static function blob($pName, $pNullable = false)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::BLOB,
                'nullable' => $pNullable
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param array $pValues
     * @param string $pDefault
     * @return Column
     */
    public static function set($pName, array $pValues, $pDefault = null)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::SET,
                'value' => $pValues,
                'default' => $pDefault
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param array $pValues
     * @param string $pDefault
     * @return Column
     */
    public static function enum($pName, array $pValues, $pDefault = null)
    {
        return static::create(
            $pName, 
            [
                'type' => Column::ENUM,
                'value' => $pValues,
                'default' => $pDefault
            ]
        );
    }
    
    /**
     * @param string $pName
     * @param array $pDefinition
     * @return Column
     * @throws \InvalidArgumentException
     */
    public static function create($pName, array $pDefinition)
    {
        $column = new Column($pName);
        
        if(!isset($pDefinition['type']))
        {
            throw new \InvalidArgumentException(sprintf('The type of "%s" column is not defined.', $pName));
        }
        
        // Type
        $column->setType($pDefinition['type']);
        
        // Value(s)
        if(!empty($pDefinition['value']))
        {
            $column->setValue($pDefinition['value']);
        }
        
        // Default value
        if(isset($pDefinition['default']))
        {
            $column->setDefault($pDefinition['default']);
        }
        
        // Collating
        if(!empty($pDefinition['collating']))
        {
            $column->setCollating($pDefinition['collating']);
        }
        
        // Attribute
        if(!empty($pDefinition['attribute']))
        {
            $column->setAttribute($pDefinition['attribute']);
        }
        
        // Nullable
        if(isset($pDefinition['nullable']))
        {
            $column->setNullable($pDefinition['nullable']);
        }
        
        // Auto increment
        if(isset($pDefinition['autoIncrement']))
        {
            $column->setAutoIncrement($pDefinition['autoIncrement']);
        }
        
        // Comment
        if(!empty($pDefinition['comment']))
        {
            $column->setComment($pDefinition['comment']);
        }
        
        return $column;
    }
}
