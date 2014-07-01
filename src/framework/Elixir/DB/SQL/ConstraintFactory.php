<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Constraint;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ConstraintFactory
{
    /**
     * @param Column|string|array $pColumns
     * @return Constraint
     */
    public static function index($pColumns)
    {
        return static::create($pColumns, ['type' => Constraint::INDEX]);
    }
    
    /**
     * @param Column|string|array $pColumns
     * @return Constraint
     */
    public static function primary($pColumns)
    {
        return static::create($pColumns, ['type' => Constraint::PRIMARY]);
    }
    
    /**
     * @param Column|string|array $pColumns
     * @return Constraint
     */
    public static function unique($pColumns)
    {
        return static::create($pColumns, ['type' => Constraint::UNIQUE]);
    }
    
    /**
     * @param Column|string|array $pColumns
     * @return Constraint
     */
    public static function fullText($pColumns)
    {
        return static::create($pColumns, ['type' => Constraint::FULLTEXT]);
    }
    
    /**
     * @param Column|string $pColumn
     * @param string $pReferenceTable
     * @param string $pReferenceColumn
     * @param string $pName
     * @param string $pOnDeleteRule
     * @param string $pOnUpdateRule
     * @return Constraint
     */
    public static function foreign($pColumn, 
                                   $pReferenceTable, 
                                   $pReferenceColumn,
                                   $pName = null,
                                   $pOnDeleteRule = null,
                                   $pOnUpdateRule = null)
    {
        return static::create(
            $pColumn, 
            [
                'type' => Constraint::FOREIGN_KEY,
                'referenceTable' => $pReferenceTable,
                'referenceColumn' => $pReferenceColumn,
                'name' => $pName,
                'onDeleteRule' => $pOnDeleteRule,
                'onUpdateRule' => $pOnUpdateRule,
            ]
        );
    }
    
    /**
     * @param Column|string|array $pColumns
     * @param array $pDefinition
     * @return Constraint
     * @throws \InvalidArgumentException
     */
    public static function create($pColumns, array $pDefinition)
    {
        $constraint = new Constraint($pColumns);
        
        if(empty($pDefinition['type']))
        {
            throw new \InvalidArgumentException(sprintf('The type of "%s" constraint is not defined.', $constraint->getColumns()[0]));
        }
        
        if($pDefinition['type'] == Constraint::FOREIGN_KEY)
        {
            if(empty($pDefinition['referenceTable']) || empty($pDefinition['referenceColumn']))
            {
                throw new \InvalidArgumentException(sprintf('The reference of "%s" constraint is not defined.', $constraint->getColumns()[0]));
            }
        }
        
        // Type
        $constraint->setType($pDefinition['type']);
        
        // Name
        if(!empty($pDefinition['name']))
        {
            $constraint->setName($pDefinition['name']);
        }
        
        // Reference table
        if(!empty($pDefinition['referenceTable']))
        {
            $constraint->setReferenceTable($pDefinition['referenceTable']);
        }
        
        // Reference column
        if(!empty($pDefinition['referenceColumn']))
        {
            $constraint->setReferenceColumn($pDefinition['referenceColumn']);
        }
        
        // Delete rule
        if(!empty($pDefinition['onDeleteRule']))
        {
            $constraint->setOnDeleteRule($pDefinition['onDeleteRule']);
        }
        
        // Delete rule
        if(!empty($pDefinition['onUpdateRule']))
        {
            $constraint->setOnUpdateRule($pDefinition['onUpdateRule']);
        }
        
        return $constraint;
    }
}