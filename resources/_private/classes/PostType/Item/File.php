<?php

namespace Isatech\PostType\Item;

class File extends ItemAbstract
{
    protected $_template = 'file';
    protected $_folder;
    protected $_filename;
    
    public function __construct($pName) 
    {
        parent::__construct($pName);
        
        add_action('post_edit_form_tag', function()
        {
            echo ' enctype="multipart/form-data"';
        });
    }
    
    public function setFolder($pValue)
    {
        $this->_folder = $pValue;
    }
    
    public function getFolder()
    {
        return $this->_folder;
    }
    
    public function setFileName($pValue)
    {
        $this->_filename = $pValue;
    }
    
    public function getFileName()
    {
        return $this->_filename;
    }
    
    public function setAttributes(array $pValue)
    {
        $this->_attributes = $pValue;
        $this->_attributes['name'] = $this->_name;
        $this->_attributes['type'] = 'file';
    }

    public function save($pPostId)
    {
        $this->_postId = $pPostId;
        
        if(isset($_FILES[$this->_name]) && !empty($_FILES[$this->_name]['name']))
        {
            $success = true;
            $file = $_FILES[$this->_name];
            
            if(null !== $this->_validation)
            {
                $v = $this->_validation;
                
                if(false === $v($file, $this->_errors))
                {
                    $success = false;
                }
            }
            
            if($success)
            {
                $uploadDir = wp_upload_dir();
                $baseDir = $uploadDir['basedir'];

                if(null !== $this->_folder)
                {
                    $path = $baseDir . '/' . $this->_folder . '/';

                    if(!file_exists($path))
                    {
                        mkdir($path, 0777);
                    }

                    $filename = pathinfo($file['name'], PATHINFO_FILENAME);

                    if(null !== $this->_filename)
                    {
                        if($this->_filename instanceof \Closure)
                        {
                            $n = $this->_filename;
                            $filename = $n($pPostId);
                        }
                        else
                        {
                            $filename = $this->_filename;
                        }
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

                    if(!move_uploaded_file($file['tmp_name'], $path . $filename . '.' . $ext))
                    {
                        $this->_errors[] = __('Error during upload', THEME_TEXT_DOMAIN);
                        return false;
                    }
                    
                    $value = $this->_folder . '/' . $filename . '.' . $ext;
                    $file = $path . $filename . '.' . $ext;
                }
                else
                {
                    $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));

                    if(false !== $upload['error'])
                    {
                        $this->_errors[] = __('Error during upload', THEME_TEXT_DOMAIN);
                        return false;
                    }
                    
                    $value = str_replace($baseDir . '/', '', $file);
                    $file = $upload['file'];
                }
                
                if(null !== $this->_filter)
                {
                    $f = $this->_filter;
                    $f($file);
                }
                
                update_post_meta($this->_postId, $this->_name, $value);
            }
            
            return $success;
        }
        else if($this->_required && $this->isEmpty())
        {
            $this->_errors[] = __('Required field', THEME_TEXT_DOMAIN);
            return false;
        }
        
        return true;
    }
}