<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Column;
use Elixir\DB\SQL\Constraint;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Create extends SQLAbstract
{
    /**
     * @var string 
     */
    protected $_table;
    
    /**
     * @var boolean 
     */
    protected $_temporary = false;
    
    /**
     * @var array 
     */
    protected $_columns = [];
    
    /**
     * @var array 
     */
    protected $_constraints = [];
    
    /**
     * @var array 
     */
    protected $_options = [];
    
    /**
     * @param string $pTable
     */
    public function __construct($pTable = null) 
    {
        if(null !== $pTable)
        {
            $this->table($pTable);
        }
    }
    
    /**
     * @param string $pTable
     * @return Create
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param boolean $pValue
     * @return Create
     */
    public function temporary($pValue)
    {
        $this->_temporary = (bool)$pValue;
        return $this;
    }
    
    /**
     * @param Column $pColumn
     * @return Create
     */
    public function column(Column $pColumn)
    {
        $this->_columns[] = $pColumn;
        return $this;
    }
    
    /**
     * @param Constraint $pConstraint
     * @return Create
     */
    public function constraint(Constraint $pConstraint)
    {
        if($pConstraint->getType() == Constraint::PRIMARY)
        {
            foreach($this->_constraints as $constraint)
            {
                if($constraint->getType() == Constraint::PRIMARY)
                {
                    $constraint->addColumn($pConstraint->getName());
                    return;
                }
            }
        }
        
        $this->_constraints[] = $pConstraint;
        return $this;
    }
    
    /**
     * @param Column $pColumn
     * @return Create
     */
    public function option($pOption, $pValue = null)
    {
        $this->_options[$pOption] = $pValue;
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return Create
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'columns':
                $this->_columns = [];
            break;
            case 'constraints':
                $this->_constraints = [];
            break;
            case 'options':
                $this->_options = [];
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $sql = 'CREATE ' . "\n";
        $sql .= $this->renderTemporary();
        $sql .= 'TABLE ' . "\n";
        $sql .= $this->_table . ' ' . "\n";
        $sql .= $this->renderColumns();
        $sql .= $this->renderOptions();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderTemporary()
    {
        $sql = '';
        
        if($this->_temporary)
        {
            $sql .= 'TEMPORARY ' . "\n";
        }
        
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderColumns()
    {
        $sql = '(';
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
                $col .= ' ' . sprintf(
                    'CHARACTER SET %s COLLATE %s', 
                    substr($collating, 0, strpos($collating, '_')), 
                    $collating
                );
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
        
        $sql .= implode(', ' . "\n", $columns);
        
        // Constraints
        foreach($this->_constraints as $constraint)
        {   
            if($constraint->getType() == Constraint::PRIMARY)
            {
                $sql .= ', ' . "\n" . 'PRIMARY KEY (' . implode(', ', $constraint->getColumns()) . ')';
            }
            else if($constraint->getType() == Constraint::FOREIGN_KEY)
            {
                $sql .= ', ' . "\n" . 'CONSTRAINT ' . $constraint->getName() . ' ';
                $sql .= 'FOREIGN KEY (' . $constraint->getColumns()[0] . ') ';
                $sql .= 'REFERENCES ' . $constraint->getReferenceTable() . '(' . $constraint->getReferenceColumn() . ') ';
                $sql .= 'ON DELETE ' . $constraint->getOnDeleteRule() . ' ';
                $sql .= 'ON UPDATE ' . $constraint->getOnUpdateRule();
            }
            else
            {
                foreach($constraint->getColumns() as $column)
                {
                    $type = $constraint->getType() != Constraint::INDEX ? $constraint->getType() . ' KEY' : 'KEY';
                    $sql .= ', ' . "\n" . $type . ' ' . $column . '(' . $column . ')';
                }
            }
        }
        
        $sql .= ') ' . "\n";
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderOptions()
    {
        $sql = '';
        
        if(count($this->_options) > 0)
        {
            $options = [];

            foreach($this->_options as $key => $value)
            {
                $options[] = $key . ' = ' . $this->quote($value);
            }
            
            $sql .= implode(' ' . "\n", $options);
        }
        
        return $sql;
    }
}