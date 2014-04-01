<?php

namespace Elixir\Filter;

use Elixir\Util\Image;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Crop extends FilterAbstract
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
        
        if(!$image->crop(isset($pOptions['wEnd']) ? $pOptions['wEnd'] : 0,
                         isset($pOptions['hEnd']) ? $pOptions['hEnd'] : 0))
        {
            $error = true;
        }
        
        if(!$image->save())
        {
            $error = true;
        }
        
        if($error)
        {
            throw new \RuntimeException('The filter "\Elixir\Filter\Crop" was not performed.');
        }
        
        return $pContent;
    }
}