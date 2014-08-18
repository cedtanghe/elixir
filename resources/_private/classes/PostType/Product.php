<?php

namespace Isatech\PostType;

use Isatech\PostType\Container\Container;
use Isatech\PostType\Container\ContainerFactory;
use Isatech\PostType\Item\ProductCustomers;
use Isatech\PostType\Metabox\Metabox;
use Isatech\PostType\Metabox\MetaboxFactory;

class Product extends PostTypeAbstract
{
    public static function activeWCOverridingJS()
    {
        add_action(
            'wp_enqueue_scripts', 
            function()
            {
                global $wp_scripts; 

                foreach($wp_scripts->registered as $key => &$script)
                {
                    if(substr($key, 0, 3) == 'wc-')
                    {
                        $pos = strpos($script->src, '/plugins/woocommerce/assets/js/frontend/');

                        if(false !== $pos)
                        {
                            $file = str_replace(substr($script->src, 0, $pos + strlen('/plugins/woocommerce/assets/js/frontend/')), '/woocommerce/assets/js/frontend/', $script->src);

                            if(WP_DEBUG)
                            {
                                $file = str_replace('.min', '', $file);
                            }
                            
                            if(file_exists(get_template_directory() . $file))
                            {
                                $script->src = get_template_directory_uri() . $file;
                            }
                        }
                    }
                }
            }
        );
    }
    
    public function __construct() 
    {
        parent::__construct('product', 'page');
        
        $this->removeExistingMetabox('postcustom', 'product', 'normal');
        $this->removeExistingMetabox('postexcerpt', 'product', 'normal');
        
        /************ MEDIA METABOX ************/
        
        $m = new Metabox('supplementaries_infos');
        $m->setTitle(__('Image ou vidéo de l\'entête', THEME_TEXT_DOMAIN));
        $m->setTemplate('product-media');
        $m->setValidation(
            function($pMetabox, $pScreenError)
            {
                if($pMetabox->getItem('choice_media')->getValue() == 'image' && $pMetabox->getItem('image')->isEmpty())
                {
                    $pScreenError->add('image', __('Field is required', THEME_TEXT_DOMAIN));
                    return false;
                }
                else if($pMetabox->getItem('choice_media')->getValue() == 'video' && $pMetabox->getItem('video')->isEmpty())
                {
                    $pScreenError->add('video', __('Field is required', THEME_TEXT_DOMAIN));
                    return false;
                }
                
                return true;
            }
        );
                
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\File',
                    'name' => 'image',
                    'folder' => 'product',
                    'filename' => function($pPostId)
                    {
                        return $pPostId;
                    },
                    'validator' => function($pValue, array &$pErrors)
                    {
                        $supportedTypes = array('image/jpeg');

                        $result = wp_check_filetype($pValue['name']);
                        $uploadedType = $result['type'];

                        if(in_array($uploadedType, $supportedTypes))
                        {
                            return true;
                        }
                        else
                        {
                            $pErrors[] = __('Type mime incorrect', THEME_TEXT_DOMAIN);
                            return false;
                        }
                    },
                    'filter' => function($pValue)
                    {
                        $image = wp_get_image_editor($pValue);
                        $image->resize(300, 300);
                        $image->save($pValue);
                    }
                ]
            )
        );
                
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\Input',
                    'name' => 'video',
                    'attributes' => [
                        'placeholder' => __('URL Youtube', THEME_TEXT_DOMAIN)
                    ],
                    'validator' => function($pValue, array &$pErrors)
                    {
                        if(false === filter_var($pValue, FILTER_VALIDATE_URL))
                        {
                            $pErrors[] = __('URL is not valid', THEME_TEXT_DOMAIN);
                            return false;
                        }

                        return true;
                    },
                    'filter' => function($pValue)
                    {
                        return esc_url($pValue);
                    }
                ]
            )
        );
                
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\Radio',
                    'name' => 'choice_media',
                    'data' => [
                        'media' => __('Je choisis ce format', THEME_TEXT_DOMAIN),
                        'video' => __('Je choisis ce format', THEME_TEXT_DOMAIN)
                    ],
                    'required' => true
                ]
            )
        );
        
        $this->addMetabox($m);
        
        /************ PROBLEMS AND SOLUTIONS METABOX ************/
        
        $m = new Metabox('problem_and_solution');
        $m->setTitle(__('Description "problème" et "solution"', THEME_TEXT_DOMAIN));
        $m->setTemplate('product-problem-and-solution');
        
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\Editor',
                    'name' => 'problem',
                    'label' => __('Votre problème', THEME_TEXT_DOMAIN)
                ]
            )
        );
                
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\Editor',
                    'name' => 'solution',
                    'label' => __('Notre solution', THEME_TEXT_DOMAIN)
                ]
            )
        );
        
        $this->addMetabox($m);
        
        /************ SUB TITLE CONTAINER ************/
        
        $c = new Container('title-extand');
        
        $c->addItem(
            ContainerFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\Input',
                    'name' => 'subtitle',
                    'required' => true,
                    'label' => __('Sous titre', THEME_TEXT_DOMAIN),
                    'filter' => function($pValue)
                    {
                        return sanitize_text_field($pValue);
                    }
                ]
            )
        );
        
        $this->addContainer($c, 'title');
        
        /************ CUSTOMER METABOX ************/
        
        $m = new Metabox('customer');
        $m->setTitle(__('Clients du produits', THEME_TEXT_DOMAIN));
        
        $m->addItem(new ProductCustomers());
        
        $this->addMetabox($m);
        
        /************ SETTINGS ************/
        
        $this->extendForm();
    }
    
    protected function extendForm() 
    {
        add_action(
            'woocommerce_product_after_variable_attributes', 
            function($pLoop, $pVariationData)
            {
                ?>
                <tr>
                    <td>
                        <?php
                        woocommerce_wp_checkbox( 
                            [
                                'id' => '_contact[' . $pLoop . ']', 
                                'description' => __('Necéssite un contact client', THEME_TEXT_DOMAIN),
                                'label' => '',
                                'value' => isset($pVariationData['_contact']) ? $pVariationData['_contact'][0] : '', 
                            ]
                        );
                        ?>
                    </td>
                </tr>
                <?php
            },
            10,
            2
        );
            
        add_action(
            'woocommerce_product_after_variable_attributes_js', 
            function()
            {
                ?>
                <tr>
                    <td>
                        <?php
                        woocommerce_wp_checkbox( 
                            [
                                'id' => '_contact[' . $pLoop . ']', 
                                'description' => __('Necéssite un contact client', THEME_TEXT_DOMAIN),
                                'label' => '',
                                'value' => isset($pVariationData['_contact']) ? $pVariationData['_contact'][0] : '', 
                            ]
                        );
                        ?>
                    </td>
                </tr>
                <?php
            }
        );
            
        add_action(
            'woocommerce_process_product_meta_variable', 
            function($postId)
            {
                if(isset($_POST['variable_sku'])) 
                {
                    $sku = $_POST['variable_sku'];
                    $postId = $_POST['variable_post_id'];
                    $checkbox = $_POST['_contact'];
                    
                    for($i = 0; $i < count($sku); $i++)
                    {
                        $id = (int)$postId[$i];
                        
                        if(isset($checkbox[$i]))
                        {
                           update_post_meta($id, '_contact', stripslashes($checkbox[$i]));
                        }
                    }
                }
            },
            10,
            1
        );
    }
    
    public function isExistingPostType()
    {
        return true;
    }
    
    public function getDefinition()
    {
        return null;
    }
}