<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Column;
use Elixir\DB\SQL\Constraint;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AlterTable extends SQLAbstract
{
    /**
     * @var string 
     */
    const ADD_COLUMN = 'add_column';
    
    /**
     * @var string 
     */
    const MODIFY_COLUMN = 'modify_column';
    
    /**
     * @var string 
     */
    const RENAME_COLUMN = 'rename_column';
    
    /**
     * @var string 
     */
    const DROP_COLUMN = 'drop_column';
    
    /**
     * @var string 
     */
    const ADD_CONSTRAINT = 'add_constraint';
    
    /**
     * @var string 
     */
    const DROP_CONSTRAINT = 'drop_constraint';
    
    /**
     * @var string 
     */
    protected $_table;
    
    /**
     * @var string 
     */
    protected $_rename;
    
    /**
     * @var array
     */
    protected $_SQLs = [];

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
     * @return AlterTable
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param string $pTable
     * @return AlterTable
     */
    public function rename($pTable)
    {
        $this->_rename = $pTable;
        return $this;
    }
    
    /**
     * @param Column $pColumn
     * @return AlterTable
     */
    public function addColumn(Column $pColumn)
    {
        $this->_SQLs[] = [
            'specification' => self::ADD_COLUMN,
            'column' => $pColumn
        ];
        
        return $this;
    }
    
    /**
     * @param Column $pColumn
     * @param string $pOldName
     * @return AlterTable
     */
    public function modifyColumn(Column $pColumn)
    {
        $this->_SQLs[] = [
            'specification' => self::MODIFY_COLUMN,
            'column' => $pColumn,
        ];
        
        return $this;
    }
    
    /**
     * @param Column|string $pColumn
     * @param Column|string $pOldColumn
     * @return AlterTable
     */
    public function renameColumn($pOldColumn, $pNewColumn)
    {
        $this->_SQLs[] = [
            'specification' => self::RENAME_COLUMN,
            'oldName' => $pOldColumn instanceof Column ? $pOldColumn->getName(): $pOldColumn,
            'newName' => $pNewColumn instanceof Column ? $pNewColumn->getName(): $pNewColumn
        ];
        
        return $this;
    }
    
    /**
     * @param string|Column $pColumn
     * @return AlterTable
     */
    public function dropColumn($pColumn)
    {
        $this->_SQLs[] = [
            'specification' => self::DROP_COLUMN,
            'column' => $pColumn instanceof Column ? $pColumn->getName() : $pColumn
        ];
        
        return $this;
    }
    
    public function addConstraint(Constraint $pConstraint)
    {
        $this->_SQLs[] = [
            'specification' => self::ADD_CONSTRAINT,
            'constraint' => $pConstraint
        ];
        
        return $this;
    }
    
    /**
     * @param Constraint $pConstraint
     * @param string $pType
     * @return AlterTable
     * @throws \InvalidArgumentException
     */
    public function dropConstraint($pConstraint = null, $pType = null)
    {
        if((null === $pConstraint && $pType != Constraint::PRIMARY) ||
           (null === $pType && !$pConstraint instanceof Constraint))
        {
            throw new \InvalidArgumentException('Error while drop constraint.');
        }
        
        $this->_SQLs[] = [
            'specification' => self::DROP_CONSTRAINT,
            'constraint' => $pConstraint,
            'type' => $pType ?: $pConstraint->getType()
        ];
        
        return $this;
    }

    /**
     * @param string $pPart
     * @return AlterTable
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'rename':
                $this->_rename = null;
            break;
            case 'columns':
                $i = count($this->_SQLs);
                
                while($i--)
                {
                    $SQL = $this->_SQLs[$i];
                    
                    if(in_array($SQL['specification'], [self::ADD_COLUMN, 
                                                        self::MODIFY_COLUMN, 
                                                        self::RENAME_COLUMN, 
                                                        self::DROP_COLUMN]))
                    {
                        array_splice($SQL, $i, 1);
                    }
                }
            break;
            case 'constraints':
                $i = count($this->_SQLs);
                
                while($i--)
                {
                    $SQL = $this->_SQLs[$i];
                    
                    if(in_array($SQL['specification'], [self::ADD_CONSTRAINT, 
                                                        self::DROP_CONSTRAINT]))
                    {
                        array_splice($SQL, $i, 1);
                    }
                }
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $SQLs = [];
        
        foreach($this->_SQLs as $SQL)
        {
            switch($SQL['specification'])
            {
                case self::ADD_COLUMN:
                    $SQLs[] = $this->renderAddColumn($SQL);
                break;
                case self::MODIFY_COLUMN:
                    $SQLs[] = $this->renderModifyColumn($SQL);
                break;
                case self::RENAME_COLUMN:
                    $SQLs[] = $this->renderRenameColumn($SQL);
                break;
                case self::DROP_COLUMN:
                    $SQLs[] = $this->renderDropColumn($SQL);
                break;
                case self::ADD_CONSTRAINT:
                    $SQLs[] = $this->renderAddConstraint($SQL);
                break;
                case self::DROP_CONSTRAINT:
                    $SQLs[] = $this->renderDropConstraint($SQL);
                break;
            }
        }
        
        if(null !== $this->_rename)
        {
            $SQLs[] = $this->renderRename();
        }
        
        return implode(';' . "\n", $SQLs);
    }
    
    /**
     * @return string
     */
    protected function renderAddColumn($pSQL)
    {
        $column = $pSQL['column'];
        $SQL = 'ALTER TABLE ADD ';
        
        // Name
        $SQL .= $column->getName();

        // Type
        $SQL .= ' ' . $column->getType();
        $value = $column->getValue();

        if(null !== $value)
        {
            $SQL .= '(' . $this->quote($value) . ')';
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
                $SQL .= ' ' . sprintf(
                    'CHARACTER SET %s COLLATE %s', 
                    substr($collating, 0, strpos($collating, '_')), 
                    $collating
                );
            }
            else
            {
                $SQL .= ' CHARACTER SET ' . $collating;
            }
        }

        // Nullable
        $SQL .= ' ' . ($column->isNullable() ? 'NULL' : 'NOT NULL');

        // AutoIncrement
        if($column->isAutoIncrement())
        {
            $SQL .= ' AUTO_INCREMENT PRIMARY KEY';
        }

        // Default
        $default = $column->getDefault();

        if(null !== $default)
        {
            if($default != Column::CURRENT_TIMESTAMP)
            {
                $default = $this->quote($default);
            }

            $SQL .= ' DEFAULT ' . $default;
        }

        // Comment
        $comment = $column->getComment();

        if(null !== $comment)
        {
            $SQL .= ' COMMENT ' . $this->quote($comment);
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderModifyColumn($pSQL)
    {
        $column = $pSQL['column'];
        $SQL = 'ALTER TABLE ' . $this->_table . ' MODIFY ';
        
        // Name
        $SQL .= $column->getName();

        // Type
        $SQL .= ' ' . $column->getType();
        $value = $column->getValue();

        if(null !== $value)
        {
            $SQL .= '(' . $this->quote($value) . ')';
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
                $SQL .= ' ' . sprintf(
                    'CHARACTER SET %s COLLATE %s', 
                    substr($collating, 0, strpos($collating, '_')), 
                    $collating
                );
            }
            else
            {
                $SQL .= ' CHARACTER SET ' . $collating;
            }
        }

        // Nullable
        $SQL .= ' ' . ($column->isNullable() ? 'NULL' : 'NOT NULL');

        // AutoIncrement
        if($column->isAutoIncrement())
        {
            $SQL .= ' AUTO_INCREMENT PRIMARY KEY';
        }

        // Default
        $default = $column->getDefault();

        if(null !== $default)
        {
            if($default != Column::CURRENT_TIMESTAMP)
            {
                $default = $this->quote($default);
            }

            $SQL .= ' DEFAULT ' . $default;
        }

        // Comment
        $comment = $column->getComment();

        if(null !== $comment)
        {
            $SQL .= ' COMMENT ' . $this->quote($comment);
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderRenameColumn($pSQL)
    {
        return 'ALTER TABLE ' . $this->_table . ' RENAME COLUMN ' . $pSQL['oldName'] . ' TO ' . $pSQL['newName'];
    }
    
    /**
     * @return string
     */
    protected function renderDropColumn($pSQL)
    {
        return 'ALTER TABLE ' . $this->_table . ' DROP COLUMN ' . $pSQL['column'];
    }
    
    /**
     * @return string
     */
    protected function renderAddConstraint($pSQL)
    {
        $SQL = 'ALTER TABLE ' . $this->_table . ' ADD ';
        
        $constraint = $pSQL['constraint'];
        $columns = $constraint->getColumns();

        if($constraint->getType() == Constraint::PRIMARY)
        {
            $SQL .= 'PRIMARY KEY (' . implode(', ', $columns) . ')';
        }
        else if($constraint->getType() == Constraint::FOREIGN_KEY)
        {
            $SQL .= 'CONSTRAINT ' . $constraint->getName() . ' ';
            $SQL .= 'FOREIGN KEY (' . $columns[0] . ') ';
            $SQL .= 'REFERENCES ' . $constraint->getReferenceTable() . '(' . $constraint->getReferenceColumn() . ') ';
            $SQL .= 'ON DELETE ' . $constraint->getOnDeleteRule() . ' ';
            $SQL .= 'ON UPDATE ' . $constraint->getOnUpdateRule();
        }
        else
        {
            foreach($columns as $column)
            {
                $SQL .= $constraint->getType() . ' ' . $column . '(' . $column . ')';
            }
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderDropConstraint($pSQL)
    {
        $SQL = 'ALTER TABLE ' . $this->_table . ' DROP ';
        
        switch($pSQL['type'])
        {
            case Constraint::PRIMARY:
                $SQL .= 'PRIMARY KEY';
            break;
            case Constraint::FOREIGN_KEY:
                $SQL .= 'FOREIGN KEY ' . ($pSQL['constraint'] instanceof Constraint ? 
                                          $pSQL['constraint']->getName() : 
                                          $pSQL['constraint']);
            break;
            case Constraint::INDEX:
                $SQL .= 'INDEX ' . ($pSQL['constraint'] instanceof Constraint ? 
                                    current($pSQL['constraint']->getColumns()) : 
                                    $pSQL['constraint']);
            break;
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderRename()
    {
        return 'ALTER TABLE ' . $this->_table . ' RENAME TO ' . $this->_rename;
    }
}
