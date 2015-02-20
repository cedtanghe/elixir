<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\Column;
use Elixir\DB\Query\SQL\Constraint;
use Elixir\DB\Query\SQL\SQLAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CreateTable extends SQLAbstract
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
     * @return CreateTable
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param boolean $pValue
     * @return CreateTable
     */
    public function temporary($pValue)
    {
        $this->_temporary = (bool)$pValue;
        return $this;
    }
    
    /**
     * @param Column $pColumn
     * @return CreateTable
     */
    public function column(Column $pColumn)
    {
        $this->_columns[] = $pColumn;
        return $this;
    }
    
    /**
     * @param Constraint $pConstraint
     * @return CreateTable
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
     * @return CreateTable
     */
    public function option($pOption, $pValue = null)
    {
        $this->_options[$pOption] = $pValue;
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return CreateTable
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
        $SQL = 'CREATE ' . "\n";
        $SQL .= $this->renderTemporary();
        $SQL .= 'TABLE ' . "\n";
        $SQL .= $this->_table . ' ' . "\n";
        $SQL .= $this->renderColumns();
        $SQL .= $this->renderOptions();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderTemporary()
    {
        $SQL = '';
        
        if($this->_temporary)
        {
            $SQL .= 'TEMPORARY ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @return string
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
            
            if(null !== $attribute)
            {
                if(strtoupper($attribute) != Column::UPDATE_CURRENT_TIMESTAMP)
                {
                    $SQL .= ' ' . $attribute;
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
    
    /**
     * @return string
     */
    protected function renderOptions()
    {
        $SQL = '';
        
        if(count($this->_options) > 0)
        {
            $options = [];

            foreach($this->_options as $key => $value)
            {
                $options[] = $key . ' = ' . $this->quote($value);
            }
            
            $SQL .= implode(' ' . "\n", $options);
        }
        
        return $SQL;
    }
}
