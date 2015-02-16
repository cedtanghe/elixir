<?php

namespace Elixir\DB\SQL\MySQL;

use Elixir\DB\SQL\Column;
use Elixir\DB\SQL\Constraint;
use Elixir\DB\SQL\CreateTable as BaseCreateTable;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CreateTable extends BaseCreateTable
{
    /**
     * @var boolean 
     */
    protected $_ifNotExists = false;
    
    /**
     * @param boolean $pValue
     * @return CreateTable
     */
    public function ifNotExists($pValue)
    {
        $this->_ifNotExists = (bool)$pValue;
        return $this;
    }
    
    /**
     * @see BaseCreateTable::render()
     */
    public function render()
    {
        $SQL = 'CREATE ' . "\n";
        $SQL .= $this->renderTemporary();
        $SQL .= 'TABLE ' . "\n";
        $SQL .= $this->renderIfNotExists();
        $SQL .= $this->_table . ' ' . "\n";
        $SQL .= $this->renderColumns();
        $SQL .= $this->renderOptions();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderIfNotExists()
    {
        $SQL = '';
        
        if($this->_ifNotExists)
        {
            $SQL .= 'IF NOT EXISTS ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @see BaseCreateTable::renderColumns()
     */
    protected function renderColumns()
    {
        $SQL = '(';
        $columns = [];
        
        foreach($this->_columns as $column)
        {
            // Name
            $col = $column->getName();
            
            // Type
            $col .= ' ' . $column->getType();
            $value = $column->getValue();
            
            if(null !== $value)
            {
                $col .= '(' . $this->quote($value) . ')';
            }
            
            // Attribute
            $attribute = $column->getAttribute();
            $update = false;
            
            if(null !== $attribute)
            {
                if(strtoupper($attribute) != Column::UPDATE_CURRENT_TIMESTAMP)
                {
                    $col .= ' ' . $attribute;
                }
                else
                {
                    $update = true;
                }
            }
            
            // Collating
            $collating = $column->getCollating();
            
            if(null !== $collating)
            {
                $pos = strpos($collating, '_');
                
                if(false !== $pos)
                {
                    $col .= ' ' . sprintf(
                        'CHARACTER SET %s COLLATE %s', 
                        substr($collating, 0, strpos($collating, '_')), 
                        $collating
                    );
                }
                else
                {
                    $col .= ' CHARACTER SET ' . $collating;
                }
            }
            
            // Nullable
            $col .= ' ' . ($column->isNullable() ? 'NULL' : 'NOT NULL');
            
            // AutoIncrement
            if($column->isAutoIncrement())
            {
                $col .= ' AUTO_INCREMENT ';
                $found = false;
                
                foreach($this->_constraints as $constraint)
                {
                    if($constraint->getType() == Constraint::PRIMARY)
                    {
                        $found = true;
                        break;
                    }
                }
                
                if(!$found)
                {
                    $this->constraint(new Constraint($column->getName(), Constraint::PRIMARY));
                }
            }
            
            // Default
            $default = $column->getDefault();
            
            if(null !== $default)
            {
                if($default != Column::CURRENT_TIMESTAMP)
                {
                    $default = $this->quote($default);
                }
                
                $col .= ' DEFAULT ' . $default;
            }
            
            if($update)
            {
                $col .= ' ' . $attribute;
            }
            
            // Comment
            $comment = $column->getComment();
            
            if(null !== $comment)
            {
                $col .= ' COMMENT ' . $this->quote($comment);
            }
            
            $columns[] = $col;
        }
        
        $SQL .= implode(', ' . "\n", $columns);
        
        // Constraints
        foreach($this->_constraints as $constraint)
        {   
            $columns = $constraint->getColumns();
            
            if($constraint->getType() == Constraint::PRIMARY)
            {
                $SQL .= ', ' . "\n" . 'PRIMARY KEY (' . implode(', ', $columns) . ')';
            }
            else if($constraint->getType() == Constraint::FOREIGN_KEY)
            {
                $SQL .= ', ' . "\n" . 'CONSTRAINT ' . $constraint->getName() . ' ';
                $SQL .= 'FOREIGN KEY (' . $columns[0] . ') ';
                $SQL .= 'REFERENCES ' . $constraint->getReferenceTable() . '(' . $constraint->getReferenceColumn() . ') ';
                $SQL .= 'ON DELETE ' . $constraint->getOnDeleteRule() . ' ';
                $SQL .= 'ON UPDATE ' . $constraint->getOnUpdateRule();
            }
            else
            {
                foreach($columns as $column)
                {
                    $SQL .= ', ' . "\n" . $constraint->getType() . ' ' . $column . '(' . $column . ')';
                }
            }
        }
        
        $SQL .= ') ' . "\n";
        return $SQL;
    }
}
