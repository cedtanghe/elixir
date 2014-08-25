<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\Util\Image;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Resize extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     * @throws \RuntimeException
     */
    public function filter($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        $error = false;
        $image = new Image(is_array($pContent) ? $pContent['tmp_name'] : $pContent);
        
        if(!$image->resize(isset($pOptions['wMax']) ? $pOptions['wMax'] : 0,
                           isset($pOptions['hMax']) ? $pOptions['hMax'] : 0,
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
            throw new \RuntimeException('The filter "\Elixir\Filter\Resize" was not performed.');
        }
        
        return $pContent;
    }
}
