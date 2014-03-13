<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Image
{
    /**
     * @var integer
     */
    const DEFAULT_QUALITY = -1;
    
    /**
     * @var string
     */
    const TYPE_MIME_JPG = 'image/jpeg';
    
    /**
     * @var string
     */
    const TYPE_MIME_PNG = 'image/png';
    
    /**
     * @var string
     */
    const TYPE_MIME_GIF = 'image/gif';
    
    /**
     * @var string
     */
    const RESIZE = 'resize';
    
    /**
     * @var string
     */
    const ENLARGE = 'enlarge';
    
    /**
     * @param string|resource $pImageOrResource
     * @param integer $pWidth
     * @param integer $pHeight
     * @param boolean $pRatio
     * @param string $pMode
     * @return array
     */
    public static function getSizingInfo($pImageOrResource, $pWidth = 0, $pHeight = 0, $pRatio = true, $pMode = self::RESIZE)
    {	
        try 
        {
            if(static::isGD($pImageOrResource))
            {
                $wSrc = imagesx($pImageOrResource);
                $hSrc = imagesy($pImageOrResource);
            }
            else
            {
                $imgSize = @getimagesize($pImagePath);  
                $wSrc = $imgSize[0];
                $hSrc = $imgSize[1];
            }
        } 
        catch(\Exception $e)
        {
            return null;
        }
        
        if ($pWidth == 0)
        {
            $pWidth = $wSrc;
        }

        if ($pHeight == 0)
        {
            $pHeight = $hSrc;
        }

        if($pRatio)
        {
            if($pMode == self::RESIZE)
            {
                $ratio = min($pWidth / $wSrc, $pHeight / $hSrc);

                if($ratio > 1)
                {
                    $ratio = 1;
                }

                $w = $wSrc * $ratio;
                $h = $hSrc * $ratio;  
            }
            else
            {
                $ratio = $wSrc / $hSrc;

                if (($pWidth / $pHeight) > $ratio)
                {
                    $w = $pWidth;
                    $h = $w / $ratio;
                }
                else
                {
                    $h = $pHeight;
                    $w = $ratio * $h;
                }
            }
        }
        else
        {
            $w = $pWidth;
            $h = $pHeight;
        }

        return array(
            'resized' => ($wSrc != $w) || ($hSrc != $h),
            'src' => $pImagePath,
            'srcWidth' => $wSrc,
            'width' => $w,
            'srcHeight' => $hSrc,
            'height' => $h
        );
    }
    
    /**
     * @param resource $pResource
     * @return boolean
     */
    public static function isGD($pResource)
    {
        return get_resource_type($pImageOrResource) == 'gd';
    }

    /**
     * @param resource $pResource
     * @param string $pTypeMime
     * @return Image
     */
    public static function createFromResource($pResource, $pTypeMime)
    {
        return new static($pResource, $pTypeMime);
    }
    
    /**
     * @param string $pImage
     * @return Image
     */
    public static function createFromImage($pImage)
    {
        return new static($pImage);
    }

    /**
     * @var string
     */
    protected $_originalImagePath;
    
    /**
     * @var string
     */
    protected $_mimeType;
    
    /**
     * @var resource
     */
    protected $_resource;
    
    /**
     * @param string|resource $pImageOrResource
     * @param string $pTypeMime
     * @throws \InvalidArgumentException
     */
    public function __construct($pImageOrResource, $pTypeMime = null) 
    {
        if(static::isGD($pImageOrResource))
        {
            $this->_resource = $pImageOrResource;
            
            if(null === $pTypeMime)
            {
                throw new \InvalidArgumentException('You must set the mime type of a resource.');
            }
            
            if(!in_array($pTypeMime, array(self::TYPE_MIME_JPG, 
                                           self::TYPE_MIME_PNG, 
                                           self::TYPE_MIME_GIF)))
            {
                throw new \InvalidArgumentException(sprintf('Mime type "%s" is not supported.', $pTypeMime));
            }
        }
        else
        {
            $this->_originalImagePath = $pImageOrResource;
            $this->_mimeType = File::mimeType($this->_originalImagePath);
            
            switch($this->_mimeType)
            {
                case self::TYPE_MIME_JPG:
                    $this->_resource = imagecreatefromjpeg($this->_originalImagePath);
                break;
                case self::TYPE_MIME_PNG:
                    $this->_resource = imagecreatefrompng($this->_originalImagePath);
                break;
                case self::TYPE_MIME_GIF:
                    $this->_resource = imagecreatefromgif($this->_originalImagePath);
                break;
                default:
                    throw new \InvalidArgumentException(sprintf('Mime type "%s" is not supported.', $this->_mimeType));
                break;
            }
        }
    }
    
    public function __destruct() 
    {
        imagedestroy($this->_resource);
    }

    /**
     * @return string
     */
    public function getOriginalImagePath()
    {
        return $this->_originalImagePath;
    }
    
    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }
    
    /**
     * @return integer
     */
    public function getWidth()
    {
        return imagesx($this->_resource);
    }
    
    /**
     * @return integer
     */
    public function getHeight()
    {
        return imagesy($this->_resource);
    }
    
    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * @param integer $pWMax
     * @param integer $pHMax
     * @param boolean $pRatio
     * @return boolean
     */
    public function resize($pWMax = 0, $pHMax = 0, $pRatio = true)
    {
        $infos = static::getSizingInfo($this->_resource, $pWMax, $pHMax, $pRatio, self::RESIZE);
        
        if(null !== $infos)
        {
            $ressDst = imagecreatetruecolor($infos['width'], $infos['height']);

            if(in_array($this->_mimeType, array(self::TYPE_MIME_PNG,
                                                self::TYPE_MIME_GIF)))
            {
                imagesavealpha($ressDst, true);
                $transColor = imagecolorallocatealpha($ressDst, 0, 0, 0, 127);
                imagefill($ressDst, 0, 0, $transColor);
            }
            
            $result = imagecopyresampled(
                $ressDst, 
                $this->_resource, 
                0,
                0,
                0,
                0,
                $infos['width'], 
                $infos['height'], 
                $infos['srcWidth'], 
                $infos['srcHeight']
            ); 
            
            imagedestroy($this->_resource);
            $this->_resource = $ressDst;
            
            return $result;
        }
        
        return false;
    }

    /**
     * @param integer $pWMin
     * @param integer $pHMin
     * @param boolean $pRatio
     * @return boolean
     */
    public function enlarge($pWMin = 0, $pHMin = 0, $pRatio = true)
    {
        $infos = static::getSizingInfo($this->_resource, $pWMin, $pHMin, $pRatio, self::ENLARGE);
        
        if(null !== $infos)
        {
            $ressDst = imagecreatetruecolor($infos['width'], $infos['height']);

            if(in_array($this->_mimeType, array(self::TYPE_MIME_PNG,
                                                self::TYPE_MIME_GIF)))
            {
                imagesavealpha($ressDst, true);
                $transColor = imagecolorallocatealpha($ressDst, 0, 0, 0, 127);
                imagefill($ressDst, 0, 0, $transColor);
            }
            
            $result = imagecopyresampled(
                $ressDst, 
                $this->_resource, 
                0,
                0,
                0,
                0,
                $infos['width'], 
                $infos['height'], 
                $infos['srcWidth'], 
                $infos['srcHeight']
            ); 
            
            imagedestroy($this->_resource);
            $this->_resource = $ressDst;
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * @param integer $pWEnd
     * @param integer $pHEnd
     * @return boolean
     */
    public function crop($pWEnd = 0, $pHEnd = 0)
    {
        $infos = static::getSizingInfo($this->_resource, $pWEnd, $pHEnd, false);
        
        if(null !== $infos)
        {
            $ressDst = imagecreatetruecolor($infos['width'], $infos['height']);

            if(in_array($this->_mimeType, array(self::TYPE_MIME_PNG,
                                                self::TYPE_MIME_GIF)))
            {
                imagesavealpha($ressDst, true);
                $transColor = imagecolorallocatealpha($ressDst, 0, 0, 0, 127);
                imagefill($ressDst, 0, 0, $transColor);
            }
            
            if ($pWEnd == 0 || $infos['srcWidth'] == $infos['width']) 
            {
                $wCopy = $infos['srcWidth'];
                $xSrc = 0;
                $xDst = 0;
            } 
            else 
            {
                $wCopy = $infos['width'];
                $xSrc = ($infos['srcWidth'] - $infos['width']) >> 1;
                $xDst = 0;
            }

            if ($pHEnd == 0 || $infos['srcHeight'] == $infos['height']) 
            {
                $hCopy = $infos['srcHeight'];
                $ySrc = 0;
                $yDst = 0;
            } 
            else 
            {
                $hCopy = $infos['height'];
                $ySrc = ($infos['srcHeight'] - $infos['height']) >> 1;
                $yDst = 0;
            }
            
            $result = imagecopyresampled(
                $ressDst, 
                $this->_resource, 
                $xDst, 
                $yDst, 
                $xSrc, 
                $ySrc, 
                $wCopy, 
                $hCopy, 
                $wCopy, 
                $hCopy
            );
            
            imagedestroy($this->_resource);
            $this->_resource = $ressDst;
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * @param integer $pFiltertype
     * @param integer $pArg1
     * @param integer $pArg2
     * @param integer $pArg3
     * @param integer $pArg4
     * @return boolean
     */
    public function filter($pFiltertype, $pArg1 = null, $pArg2 = null, $pArg3 = null, $pArg4 = null)
    {
        return imagefilter(
            $this->_resource,
            $pFiltertype, 
            $pArg1, 
            $pArg2, 
            $pArg3, 
            $pArg4
        );
    }
    
    /**
     * @param string $pImagePath
     * @param integer $pQuality
     * @return boolean
     */
    public function save($pImagePath = null, $pQuality = self::DEFAULT_QUALITY)
    {
        $imagePath = $pImagePath ?: $this->_originalImagePath;
        
        if(null === $imagePath)
        {
            throw new \InvalidArgumentException('You must specify the name of the exported image.');
        }

        switch($this->_mimeType)
        {
            case self::TYPE_MIME_JPG:
                $pQuality = $pQuality == self::DEFAULT_QUALITY ? 75 : $pQuality;
                imagejpeg($this->_resource, $imagePath, $pQuality);
            break;
            case self::TYPE_MIME_PNG:
                $pQuality = $pQuality == self::DEFAULT_QUALITY ? 1 : (9 - (round(($pQuality / 100) * 9)));
                imagepng($this->_resource, $imagePath, $pQuality);
            break;
            case self::TYPE_MIME_GIF:
                imagegif($this->_resource, $imagePath);
            break;
        }

        return file_exists($imagePath);
    }
}