<?php

namespace Elixir\DB\Query\SQL\MySQL;

use Elixir\DB\Query\SQL\AlterTable as BaseAlterTable;
use Elixir\DB\Query\SQL\Column;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AlterTable extends BaseAlterTable
{
    /**
     * @var string 
     */
    const COLLATING = 'collating';
    
    /**
     * @var string 
     */
    const AFTER = 'after';
    
    /**
     * @var string 
     */
    const FIRST = 'first';
    
    /**
     * @see BaseAlterTable::renameColumn()
     * @throws \InvalidArgumentException
     */
    public function renameColumn($pOldColumn, $pNewColumn)
    {
        if(!$pOldColumn instanceof Column && !$pNewColumn instanceof Column)
        {
            throw new \InvalidArgumentException('Mysql requires the definition of the column to rename it.');
        }
        
        $this->SQL[] = [
            'specification' => self::RENAME_COLUMN,
            'oldColumn' => $pOldColumn,
            'newColumn' => $pNewColumn
        ];
        
        return $this;
    }
    
    /**
     * @see AlterTable::addColumn()
     * @param string $pPrevious
     */
    public function addColumnAfter(Column $pColumn, $pPrevious)
    {
        $this->SQL[] = [
            'specification' => self::ADD_COLUMN,
            'column' => $pColumn,
            'previous' => $pPrevious instanceof Column ? $pPrevious->getName() : $pPrevious,
            'position' => self::AFTER
        ];
        
        return $this;
    }
    
    /**
     * @see AlterTable::addColumn()
     */
    public function addColumnFirst(Column $pColumn)
    {
        $this->SQL[] = [
            'specification' => self::ADD_COLUMN,
            'column' => $pColumn,
            'position' => self::FIRST
        ];
        
        return $this;
    }
    
    /**
     * @param string $pCollating
     * @return AlterTable
     */
    public function collating($pCollating)
    {
        $this->SQL[] = [
            'specification' => self::COLLATING,
            'collating' => $pCollating
        ];
        
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return AlterTable
     */
    public function reset($pPart)
    {
        parent::reset($pPart);
        
        if($pPart == 'collating')
        {
            $i = count($this->SQL);

            while($i--)
            {
                $SQL = $this->SQL[$i];

                if($SQL['specification'] == self::COLLATING)
                {
                    array_splice($SQL, $i, 1);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * @see AlterTable::render()
     */
    public function render()
    {
        $SQLs = [];
        
        foreach($this->SQL as $SQL)
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
                case self::COLLATING:
                    $SQLs[] = $this->renderColating($SQL);
                break;
            }
        }
        
        if(null !== $this->rename)
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
        $SQL = 'ALTER TABLE ' . $this->_table . ' ADD ';
        
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
        $update = false;
        
        if(null !== $attribute)
        {
            if(strtoupper($attribute) != Column::UPDATE_CURRENT_TIMESTAMP)
            {
                $SQL .= ' ' . $attribute;
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
        
        if($update)
        {
            $SQL .= ' ' . $attribute;
        }

        // Comment
        $comment = $column->getComment();

        if(null !== $comment)
        {
            $SQL .= ' COMMENT ' . $this->quote($comment);
        }
        
        if(isset($pSQL['position']))
        {
            switch($pSQL['position'])
            {
                case self::FIRST:
                    $SQL .= ' FIRST';
                break;
                case self::AFTER:
                    $SQL .= ' AFTER ' . $pSQL['previous'];
                break;
            }
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
        $update = false;
        
        if(null !== $attribute)
        {
            if(strtoupper($attribute) != Column::UPDATE_CURRENT_TIMESTAMP)
            {
                $SQL .= ' ' . $attribute;
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
        
        if($update)
        {
            $SQL .= ' ' . $attribute;
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
     * @see AlterTable::renderRenameColumn()
     */
    protected function renderRenameColumn($pSQL)
    {
        $column = null;
        
        if($pSQL['newColumn'] instanceof Column)
        {
            $newName = $pSQL['newColumn']->getName();
            $column = $pSQL['newColumn'];
        }
        else
        {
            $newName = $pSQL['newColumn'];
        }
        
        if($pSQL['oldColumn'] instanceof Column)
        {
            $oldName = $pSQL['oldColumn']->getName();
            
            if(null === $column)
            {
                $column = $pSQL['oldColumn'];
            }
        }
        else
        {
            $oldName = $pSQL['oldColumn'];
        }
        
        $SQL = 'ALTER TABLE ' . $this->_table . ' CHANGE ' . $oldName . ' ' . $newName;
        
        // Type
        $SQL .= ' ' . $column->getType();
        $value = $column->getValue();

        if(null !== $value)
        {
            $SQL .= '(' . $this->quote($value) . ')';
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderColating($pSQL)
    {
        $SQL = 'ALTER TABLE ' . $this->_table . ' CONVERT TO ';
        
        $collating = $pSQL['collating'];
        $pos = strpos($collating, '_');
                
        if(false !== $pos)
        {
            $SQL .= sprintf(
                'CHARACTER SET %s COLLATE %s', 
                substr($collating, 0, strpos($collating, '_')), 
                $collating
            );
        }
        else
        {
            $SQL .= 'CHARACTER SET ' . $collating;
        }
        
        return $SQL;
    }
}
