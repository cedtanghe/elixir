<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SQLInterface
{
    /**
     * @var string
     */
    const STAR = '*';
    
    /**
     * @var string
     */
    const QUANTIFIER_ALL = 'ALL';
    
    /**
     * @var string
     */
    const QUANTIFIER_DISTINCT = 'DISTINCT';
    
    /**
     * @var string
     */
    const COMBINE_UNION = 'UNION';
    
    /**
     * @var string
     */
    const COMBINE_UNION_ALL = 'UNION_ALL';
    
    /**
     * @var string
     */
    const COMBINE_EXPECT = 'EXPECT';
    
    /**
     * @var string
     */
    const COMBINE_INTERSECT = 'INTERSECT';
    
    /**
     * @var string
     */
    const JOIN_CROSS = 'CROSS';
    
    /**
     * @var string
     */
    const JOIN_INNER = 'INNER';
    
    /**
     * @var string
     */
    const JOIN_OUTER = 'OUTER';
    
    /**
     * @var string
     */
    const JOIN_LEFT = 'LEFT';
    
    /**
     * @var string
     */
    const JOIN_RIGHT = 'RIGHT';
    
    /**
     * @var string
     */
    const JOIN_NATURAL = 'NATURAL';
    
    /**
     * @var string
     */
    const ORDER_ASCENDING = 'ASC';
    
    /**
     * @var string
     */
    const ORDER_DESCENDING = 'DESC';
    
    /**
     * @var string
     */
    const ORDER_NONE = null;
    
    /**
     * @var string
     */
    const CONSTRAINT_TEMPORARY = 'TEMPORARY';
    
    /**
     * @var string
     */
    const CONSTRAINT_IF_NOT_EXISTS = 'IF NOT EXISTS';
    
    /**
     * @var string
     */
    const OPTION_ENGINE = 'ENGINE';
    
    /**
     * @var string
     */
    const OPTION_AUTO_INCREMENT = 'AUTO_INCREMENT';
    
    /**
     * @var string
     */
    const OPTION_COMMENT = 'COMMENT';
    
    /**
     * @var string
     */
    const OPTION_CHARSET = 'DEFAULT CHARSET';
    
    /**
     * @var string
     */
    const OPTION_WITHOUT_ROWID = 'WITHOUT ROWID';
    
    /**
     * @var string
     */
    const ENGINE_INNODB = 'InnoDB';
    
    /**
     * @var string
     */
    const ENGINE_MYISAM = 'MyISAM';
    
    /**
     * @var string
     */
    const CHARSET_UTF8 = 'utf8';
    
    /**
     * @param callable $pValue
     */
    public function setQuoteMethod(callable $pValue);
    
    /**
     * @return callable
     */
    public function getQuoteMethod();
    
    /**
     * @param mixed $pParameter
     * @return mixed
     */
    public function quote($pParameter);
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function bindValue($pKey, $pValue);
    
    /**
     * @return array
     */
    public function getBindValues();
    
    /**
     * @param string $pSQL
     * @param mixed $pValues
     * @return string
     */
    public function assemble($pSQL, $pValues = null);
    
    /**
     * @see SQLInterface::render()
     */
    public function getQuery();

    /**
     * @return string
     */
    public function render();
}
