<?php

namespace Isatech\PostType;

use Isatech\PostType\Container\Container;
use Isatech\PostType\Metabox\Metabox;
use Isatech\ScreenError;

abstract class PostTypeAbstract
{
    protected $_postType;
    protected $_type;
    protected $_JSFile;
    protected $_screenError;
    protected $_topContent;
    protected $_metaboxes = [];
    protected $_containers = [];
    protected $_removeExistingMetaboxes = [];
    protected $_removeExistingSupports = [];
    
    public function __construct($pPostType, $pType) 
    {
        $this->_postType = $pPostType;
        $this->_type = $pType;
        $this->_screenError = new ScreenError($pPostType);
        
        add_action(
            'admin_init',
            function()
            {
                if(null !== $this->_topContent)
                {
                    add_action(
                        'edit_form_top', 
                        function()
                        {
                            global $post;

                            if($post->post_type !== $this->_postType)
                            {
                                return;
                            }

                            if(is_callable($this->_topContent))
                            {
                                call_user_func($this->_topContent);
                            }
                            else
                            {
                                echo $this->_topContent;
                            }
                        }
                    );
                }
            
                foreach($this->_containers as $key => $value)
                {
                    usort(
                        $value, 
                        function($p1, $p2)
                        {
                            return ($p1->getWeight() > $p2->getWeight()) ? -1 : 1;
                        }
                    );
                    
                    add_action(
                        'edit_form_after_' . $key, 
                        function() use($value)
                        {
                            global $post;
                        
                            if($post->post_type !== $this->_postType)
                            {
                                return;
                            }

                            foreach($value as $container)
                            {
                                $container->render($post);
                            }
                        }
                    );
                }
            
                add_action(
                    'add_meta_boxes', 
                    function()
                    {
                        foreach($this->_metaboxes as $metabox)
                        {
                            $metabox->register($this->_postType);
                        }
                    }
                );
                    
                add_action(
                    'add_meta_boxes', 
                    function()
                    {
                        foreach($this->_removeExistingMetaboxes as $data)
                        {
                            call_user_func_array('remove_meta_box', $data);
                        }
                        
                        foreach($this->_removeExistingSupports as $support)
                        {
                            remove_post_type_support($this->_postType, $support);
                        }
                    },
                    100
                );
            }
        );
    }
    
    abstract public function isExistingPostType();
    abstract public function getDefinition();
    
    public function getPostType()
    {
        return $this->_postType;
    }

    public function getType()
    {
        return $this->_type;
    }
    
    public function setJSFile($pValue)
    {
        $this->_JSFile = $pValue;
    }

    public function getJSFile()
    {
        return $this->_JSFile;
    }
    
    public function setTopContent($pValue)
    {
        $this->_topContent = $pValue;
    }

    public function getTopContent()
    {
        return $this->_topContent;
    }
    
    public function removeExistingMetabox($pId, $pPage, $pContext = null)
    {
        $this->_removeExistingMetaboxes[] = [$pId, $pPage, $pContext];
    }
    
    public function getRemovedExistingMetaboxes()
    {
        return $this->_removeExistingMetaboxes;
    }
    
    public function removeExistingSupport($pSupport)
    {
        $this->_removeExistingSupports[] = $pSupport;
    }
    
    public function getRemovedExistingSupports()
    {
        return $this->_removeExistingSupports;
    }
    
    public function addMetabox(Metabox $pMetabox)
    {
        $this->_metaboxes[] = $pMetabox;
    }
    
    public function getMetaboxes()
    {
        return $this->_metaboxes;
    }
    
    public function addContainer(Container $pContainer, $pAfter = 'title')
    {
        $this->_containers[$pAfter][] = $pContainer;
    }
    
    public function getContainers($pAfter = null)
    {
        if(null === $pAfter)
        {
            return $this->_containers;
        }
        
        return $this->_containers[$pAfter];
    }
    
    public function getScreenError()
    {
        return $this->_screenError;
    }
    
    public function register() 
    {
        if(null !== $this->_JSFile)
        {
            if(is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == $this->_postType)
            {
                add_action( 
                    'admin_enqueue_scripts', 
                    function()
                    {
                        wp_enqueue_script(
                            $this->_postType . '_js_validation', 
                            get_stylesheet_directory_uri() . '/js/' . $this->_JSFile, 
                            [], 
                            '1.0', 
                            true
                        );
                    }
                );
            }
        }
        
        if(!$this->isExistingPostType())
        {
            register_post_type($this->_postType, $this->getDefinition());
        }
        
        add_action(
            'save_post', 
            function($pPostId)
            {
                if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
                {
                    return;
                }
                
                if(isset($_POST['post_type']) && $_POST['post_type'] == $this->_postType) 
                {
                    $capabilities = [];
                    
                    if($this->_type == 'page')
                    {
                        $capabilities = ['page', 'pages'];
                    }
                    else
                    {
                        $capabilities = ['post', 'posts'];
                    }
                    
                    foreach($capabilities as $capability)
                    {
                        if(!current_user_can('edit_' . $capability, $pPostId))
                        {
                            return;
                        }
                    }
                    
                    $save = $this->save($pPostId);
                    
                    if(false === $save || $this->_screenError->hasErrors())
                    {
                        global $wpdb;
                        $wpdb->update($wpdb->posts, ['post_status' => 'draft'], ['ID' => $pPostId]);

                        add_filter(
                            'redirect_post_location', 
                            function($pLocation)
                            {
                                $location = remove_query_arg('message', $pLocation);
                                $location = add_query_arg('error', 'true', $location);

                                return $location;
                            }
                        );
                    }
                }
            }
        );
        
        $this->_screenError->activate();
    }
    
    protected function save($pPostId)
    {
        $success = true;
        
        foreach($this->_metaboxes as $metabox)
        {
            if(false === $metabox->save($pPostId, $this->_screenError))
            {
                $success = false;
            }
        }
        
        foreach($this->_containers as $key => $value)
        {
            foreach($value as $container)
            {
                if(false === $container->save($pPostId, $this->_screenError))
                {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}