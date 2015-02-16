<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Expr
{
    /**
     * @param string $pExpr
     * @return Expr
     */
    public static function protect($pExpr)
    {
        return new static($pExpr);
    }

    /**
     * @var string 
     */
    protected $_expr;

    /**
     * @param string $pExpr
     */
    public function __construct($pExpr) 
    {
        $this->_expr  = $pExpr;
    }
    
    /**
     * @return string
     */
    public function getExpr()
    {
        return $this->_expr;
    }

    /**
     * @see Expr::getExpr()
     */
    public function __toString()
    {
        return $this->getExpr();
    }
}
