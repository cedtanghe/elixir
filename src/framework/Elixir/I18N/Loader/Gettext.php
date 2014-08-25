<?php

namespace Elixir\I18N\Loader;

use Elixir\I18N\Loader\LoaderInterface;

/**
 * @see Zend/I18n/Translator/Loader/Gettext.php
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Gettext implements LoaderInterface
{
    /**
     * @var resource
     */
    protected $_file;
    
    /**
     * @var boolean
     */
    protected $_littleEndian;

    /**
     * @see LoaderInterface::load()
     * @throws \InvalidArgumentException
     */
    public function load($pResource)
    {
        $this->_file = is_file($pResource) ? fopen($pResource, 'rb') : $pResource;
        $magic = fread($this->_file, 4);
        
        if($magic == "\x95\x04\x12\xde")
        {
            $this->_littleEndian = false;
        } 
        else if($magic == "\xde\x12\x04\x95") 
        {
            $this->_littleEndian = true;
        } 
        else
        {
            fclose($this->_file);
            throw new \InvalidArgumentException('This is not a valid gettext file.');
        }
        
        // Major revision
        $this->readInteger() >> 16;
        
        $numStrings = $this->readInteger();
        
        $originalStringTableOffset = $this->readInteger();
        $translationStringTableOffset = $this->readInteger();
        
        fseek($this->_file, $originalStringTableOffset);
        $originalStringTable = $this->readIntegerList(2 * $numStrings);
        
        fseek($this->_file, $translationStringTableOffset);
        $translationStringTable = $this->readIntegerList(2 * $numStrings);
        
        $data = [];
        
        for($i = 0; $i < $numStrings; ++$i) 
        {
            $sizeKey = $i * 2 + 1;
            $offsetKey = $i * 2 + 2;
            $originalStringSize = $originalStringTable[$sizeKey];
            $originalStringOffset = $originalStringTable[$offsetKey];
            $translationStringSize = $translationStringTable[$sizeKey];
            $translationStringOffset = $translationStringTable[$offsetKey];
            $originalString = [''];
            
            if($originalStringSize > 0) 
            {
                fseek($this->_file, $originalStringOffset);
                $originalString = explode("\0", fread($this->_file, $originalStringSize));
            }

            if($translationStringSize > 0) 
            {
                fseek($this->_file, $translationStringOffset);
                $translationString = explode("\0", fread($this->_file, $translationStringSize));

                if(count($originalString) > 1 && count($translationString) > 1) 
                {
                    $data[$originalString[0]] = $translationString;
                    array_shift($originalString);

                    foreach($originalString as $string) 
                    {
                        $data[$string] = '';
                    }
                } 
                else 
                {
                    $data[$originalString[0]] = $translationString[0];
                }
            }
        }
        
        unset($data['']);
        
        fclose($this->_file);
        return $data;
    }
    
    /**
     * @return integer
     */
    protected function readInteger()
    {
        $format = $this->_littleEndian ? 'Vint' : 'Nint';
        $result = unpack($format, fread($this->_file, 4));

        return $result['int'];
    }
    
    /**
     * @param integer $pNum
     * @return integer
     */
    protected function readIntegerList($pNum)
    {
        $format = $this->_littleEndian ? 'V' . $pNum : 'N' . $pNum;
        return unpack($format, fread($this->_file, 4 * $pNum));
    }
}
