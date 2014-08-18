<?php

namespace Isatech\PostType\Item;

class ProductCustomers implements ItemInterface
{
    public static function saveCustomer($pPostID, $pFile, $pDescription, $pURL)
    {
        $data = ['image' => '', 'description' => $pDescription, 'url' => $pURL];
        
        $checkFiletype = wp_check_filetype($pFile['name']);

        if(!in_array($checkFiletype['type'], array('image/jpeg')))
        {
            return false;
        }
        
        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'];
        $baseURL = $uploadDir['baseurl'];
        $path = $baseDir . '/product/customer/';
        
        $ext = pathinfo($pFile['name'], PATHINFO_EXTENSION);

        if(!file_exists($path))
        {
            mkdir($path, 0777);
        }

        do
        {
            $filename = uniqid();
        }
        while(file_exists($path . $filename . '.' . $ext));
        
        $file = $path . $filename . '.' . $ext;
        
        if(!move_uploaded_file($pFile['tmp_name'], $file))
        {
            return false;
        }

        $image = wp_get_image_editor($file);
        $image->resize(200, 200);
        $image->save($file);
        
        $data['image'] = '/product/customer/' . $filename . '.' . $ext;
        
        add_post_meta($pPostID, 'product-customers', json_encode($data));
        $data['file'] = $baseURL . $data['image'];
        
        return $data;
    }
    
    public static function deleteCustomer($pPostID, $pImage)
    {
        $customers = get_post_meta($pPostID, 'product-customers', false);
        
        foreach($customers as $customer)
        {
            $decoded = json_decode($customer, true);
            
            if($decoded['image'] == $pImage)
            {
                delete_post_meta($pPostID, 'product-customers', $customer);
                return true;
            }
        }
        
        return false;
    }

    protected $_name = 'product-customers';
    protected $_required = false;
    protected $_validation;
    protected $_filter;
    protected $_errors = [];
    protected $_template = 'product-customers';
    protected $_postId;
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function isRequired()
    {
        return $this->_required;
    }
    
    public function setPostId($pValue)
    {
        $this->_postId = $pValue;
    }
    
    public function getPostId()
    {
        return $this->_postId;
    }

    public function save($pPostId)
    {
        $this->_postId = $pPostId;
        // Use ajax, it does not save here
        
        return true;
    }
    
    public function isEmpty() 
    {
        return count($this->getValue()) == 0;
    }
    
    public function getValue() 
    {
        $values = get_post_meta($this->_postId, $this->_name, false);
        
        foreach($values as &$customer)
        {
            $customer = json_decode($customer, true);
        }
        
        return $values;
    }
    
    public function render()
    {
        $this->_errors = [];
        include get_template_directory() . '/templates/item/' . $this->_template . '.php';
    }
    
    public function getErrors()
    {
        return $this->_errors;
    }
}