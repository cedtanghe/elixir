<?php

namespace Elixir\Routing\Matcher;

use Elixir\Routing\Collection;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class QueryMatcher extends URLMatcher
{
    /**
     * @var string 
     */
    protected $_queryKey = 'r';

    /**
     * @return string
     */
    public function getQueryKey()
    {
        return $this->_queryKey;
    }

    /**
     * @param string $pValue
     */
    public function setQueryKey($pValue)
    {
        $this->_queryKey = $pValue;
    }

    /**
     * @see URLMatcher::match()
     * @throws \RuntimeException
     */
    public function match(Collection $pCollection, $pURL)
    {
        if(null === $this->_request)
        {
            throw new \RuntimeException('Request class is not defined.');
        }
        
        $URL = rawurldecode($this->_request->getQuery($this->_queryKey));
        
        if(empty($URL))
        {
            $URL = '';
        }
        
        return parent::match($pCollection, $URL);
    }
}