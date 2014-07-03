<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\AlterTable as BaseAlterTable;
use Elixir\DB\SQL\Column;
use Elixir\DB\SQL\Constraint;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AlterTable extends BaseAlterTable
{
    /**
     * @see BaseAlterTable::modifyColumn()
     * @throws \LogicException
     */
    public function modifyColumn(Column $pColumn)
    {
        throw new \LogicException('Not implemented in sqlite.');
    }
    
    /**
     * @see BaseAlterTable::renameColumn()
     * @throws \LogicException
     */
    public function renameColumn($pOldColumn, $pNewColumn)
    {
        throw new \LogicException('Not implemented in sqlite.');
    }
    
    /**
     * @see BaseAlterTable::dropColumn()
     * @throws \LogicException
     */
    public function dropColumn($pColumn)
    {
        throw new \LogicException('Not implemented in sqlite.');
    }
    
    /**
     * @see BaseAlterTable::addConstraint()
     * @throws \LogicException
     */
    public function addConstraint(Constraint $pConstraint)
    {
        throw new \LogicException('Not implemented in sqlite.');
    }
    
    /**
     * @see BaseAlterTable::dropConstraint()
     * @throws \LogicException
     */
    public function dropConstraint($pConstraint = null, $pType = null)
    {
        throw new \LogicException('Not implemented in sqlite.');
    }
    
    /**
     * @see BaseAlterTable::renderAddColumn()
     */
    protected function renderAddColumn($pSQL)
    {
        $column = $pSQL['column'];
        $SQL = 'ALTER TABLE ' . $this->_table . ' ADD COLUMN ';
        
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
            $SQL .= ' ' . $attribute;
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
        
        return $SQL;
    }
}
