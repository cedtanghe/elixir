<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\Util\Image;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Enlarge extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     * @throws \RuntimeException
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        $error = false;
        $image = new Image(is_array($pContent) ? $pContent['tmp_name'] : $pContent);
        
        if(!$image->enlarge(isset($pOptions['wMin']) ? $pOptions['wMin'] : 0,
                            isset($pOptions['hMin']) ? $pOptions['hMin'] : 0,
                            isset($pOptions['ratio']) ? $pOptions['ratio'] : true))
        {
            $error = true;
        }
        
        if(!$image->save())
        {
            $error = true;
        }
        
        if($error)
        {
            throw new \RuntimeException('The filter "\Elixir\Filter\Enlarge" was not performed.');
        }
        
        return $pContent;
    }
}