<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @see Zend/Escaper/Escaper.php
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Escaper extends FilterAbstract
{
    /**
     * @var string
     */
    const HTML = 'html';
    
    /**
     * @var string
     */
    const UNESCAPE_HTML = 'unescape_html';
    
    /**
     * @var string
     */
    const HTML_ATTR = 'html_attr';
    
    /**
     * @var string
     */
    const XML = 'xml';
    
    /**
     * @var string
     */
    const XML_ATTR = 'xml_attr';
    
    /**
     * @var string
     */
    const JS = 'js';
    
    /**
     * @var string
     */
    const CSS = 'css';
    
    /**
     * @var string
     */
    const URL = 'url';
    
    /**
     * @var string
     */
    const UNESCAPE_URL = 'unescape_url';
    
    /**
     * @var string
     */
    protected $_encoding;
    
    /**
     * @param string $pEncoding 
     */
    public function __construct($pEncoding = 'UTF-8')
    {
        $this->_encoding = $pEncoding;
    }
    
    /**
     * @return string 
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        $strategy = isset($pOptions['strategy']) ? $pOptions['strategy'] : self::HTML;
        $encoding = $this->_encoding;
        
        if(isset($pOptions['encoding']))
        {
            $this->_encoding = $pOptions['encoding'];
        }
        
        switch($strategy)
        {
            case self::HTML:
                return $this->escapeHTML($pContent, isset($pOptions['flags']) ? $pOptions['flags'] : null);
            break;
            case self::UNESCAPE_HTML:
                return $this->unescapeHTML($pContent, isset($pOptions['flags']) ? $pOptions['flags'] : null);
            break;
            case self::XML:
                return $this->escapeXML($pContent);
            break;
            case self::HTML_ATTR:
                return $this->escapeHTMLAttr($pContent);
            break;
            case self::XML_ATTR:
                return $this->escapeXMLAttr($pContent);
            break;
            case self::JS:
                return $this->escapeJS($pContent);
            break;
            case self::CSS:
                return $this->escapeCSS($pContent);
            break;
            case self::URL:
                return $this->escapeURL($pContent);
            break;
            case self::UNESCAPE_URL:
                return $this->unescapeURL($pContent);
            break;
        }
        
        $this->_encoding = $encoding;
    }
    
    /**
     * @param string $pValue
     * @return string 
     */
    public function escapeJS($pValue)
    {
        $string = $pValue;
        
        if($this->_encoding != 'UTF-8')
        {
            $string = $this->convertEncoding($string, 'UTF-8', $this->_encoding);
        }
        
        if ('' === $string || ctype_digit($string)) 
        {
            return $string;
        }
        
        $result = preg_replace_callback('/[^a-z0-9,\._]/iSu', array($this, 'JSCallback'), $string);
        
        if($this->_encoding != 'UTF-8')
        {
            $result = $this->convertEncoding($result, $this->_encoding, 'UTF-8');
        }
        
        return $result;
    }
    
    /**
     * @param array $pMatches
     * @return string 
     */
    protected function JSCallback(array $pMatches)
    {
        $chr = $pMatches[0];
        
        if (strlen($chr) == 1) 
        {
            return sprintf('\\x%02X', ord($chr));
        }
        
        $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
        return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
    }
    
    /**
     * @param string $pValue
     * @return string 
     */
    public function escapeCSS($pValue)
    {
        $string = $pValue;
        
        if($this->_encoding != 'UTF-8')
        {
            $string = $this->convertEncoding($string, 'UTF-8', $this->_encoding);
        }
        
        if ('' === $string || ctype_digit($string)) 
        {
            return $string;
        }
        
        $result = preg_replace_callback('/[^a-z0-9]/iSu', array($this, 'CSSCallback'), $string);
        
        if($this->_encoding != 'UTF-8')
        {
            $result = $this->convertEncoding($result, $this->_encoding, 'UTF-8');
        }
        
        return $result;
    }
    
    /**
     * @param array $pMatches
     * @return string 
     */
    protected function CSSCallback(array $pMatches)
    {
        $chr = $pMatches[0];
        
        if (strlen($chr) == 1) 
        {
            $ord = ord($chr);
        } 
        else
        {
            $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
            $ord = hexdec(bin2hex($chr));
        }
        
        return sprintf('\\%X ', $ord);
    }
    
    /**
     * @param string $pValue
     * @return string 
     */
    public function escapeURL($pValue)
    {
        return rawurlencode($pValue);
    }
    
    /**
     * @param string $pValue
     * @return string 
     */
    public function unescapeURL($pValue)
    {
        return rawurldecode($pValue);
    }
    
    /**
     * @param string $pValue
     * @param integer $pFlags
     * @return string 
     */
    public function escapeHTML($pValue, $pFlags = null)
    {
        if(null === $pFlags)
        {
            $pFlags = ENT_QUOTES;

            if(defined('ENT_SUBSTITUTE'))
            {
                $pFlags |= ENT_SUBSTITUTE;
            }
        }
        
        return htmlspecialchars($pValue, $pFlags, $this->_encoding);
    }
    
    /**
     * @param string $pValue
     * @param integer $pFlags
     * @return string 
     */
    public function unescapeHTML($pValue, $pFlags = null)
    {
        if(null === $pFlags)
        {
            $pFlags = ENT_QUOTES | ENT_HTML401;
        }
        
        return htmlspecialchars_decode($pValue, $pFlags);
    }
    
    /**
     * @param string $pValue
     * @return string 
     */
    public function escapeHTMLAttr($pValue)
    {
        $string = $pValue;
        
        if($this->_encoding != 'UTF-8')
        {
            $string = $this->convertEncoding($string, 'UTF-8', $this->_encoding);
        }
        
        if ('' === $string || ctype_digit($string)) 
        {
            return $string;
        }
        
        $result = preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', array($this, 'HTLMAttrCallback'), $string);
        
        if($this->_encoding != 'UTF-8')
        {
            $result = $this->convertEncoding($result, $this->_encoding, 'UTF-8');
        }
        
        return $result;
    }
    
    /**
     * @param array $pMatches
     * @return string 
     */
    protected function HTLMAttrCallback(array $pMatches)
    {
        $chr = $pMatches[0];
        $ord = ord($chr);
        
        if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) 
        {
            return '&#xFFFD;';
        }
        
        if (strlen($chr) > 1) 
        {
            $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
        }

        $hex = bin2hex($chr);
        $ord = hexdec($hex);
        
        $htmlNamedEntityMap = array(34 => 'quot',
                                    38 => 'amp',
                                    60 => 'lt',
                                    62 => 'gt');
        
        if (isset($htmlNamedEntityMap[$ord]))
        {
            return '&' . $htmlNamedEntityMap[$ord] . ';';
        }
        
        if ($ord > 255) 
        {
            return sprintf('&#x%04X;', $ord);
        }
        
        return sprintf('&#x%02X;', $ord);
    }

    /**
     * @see Escaper::escapeHTML()
     */
    public function escapeXML($pValue)
    {
        return $this->escapeHTML($pValue);
    }
    
    /**
     * @see Escaper::escapeHTMLAttr()
     */
    public function escapeXMLAttr($pValue)
    {
        return $this->escapeHTMLAttr($pValue);
    }
    
    /**
     * @param string $pValue
     * @param string $pTo
     * @param string $pFrom
     * @return string 
     * @throws \RuntimeException
     */
    public function convertEncoding($pValue, $pTo, $pFrom)
    {
        if(function_exists('iconv'))
        {
            return iconv($pFrom, $pTo, $pValue);
        }
        else if(function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($pValue, $pTo, $pFrom);
        }
        
        throw new \RuntimeException('Convert encoding function require the "iconv" or "mbstring" extension.');
    }
}
