<?php

namespace Isatech\PostType;

use Isatech\PostType\Metabox\Metabox;
use Isatech\PostType\Metabox\MetaboxFactory;

class ProductPresentation extends PostTypeAbstract
{
    public function __construct() 
    {
        parent::__construct('product-presentation', 'page');
        
        /************ SUPPLEMENTARIES INFOS METABOX ************/
        
        $m = new Metabox('supplementaries_infos');
        $m->setTitle(__('Infos complementaires', THEME_TEXT_DOMAIN));
        $m->setTemplate('product-presentation-infos');
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
                    'type' => 'Isatech\PostType\Item\Input',
                    'name' => 'subtitle',
                    'label' => __('Sous titre', THEME_TEXT_DOMAIN),
                    'required' => true,
                    'filter' => function($pValue)
                    {
                        return sanitize_text_field($pValue);
                    }
                ]
            )
        );
                
        $m->addItem(
            MetaboxFactory::createItem(
                [
                    'type' => 'Isatech\PostType\Item\File',
                    'name' => 'image',
                    'folder' => 'product-presentation',
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
                        return sanitize_text_field($pValue);
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
    }
    
    public function isExistingPostType()
    {
        return false;
    }
    
    public function getDefinition()
    {
        return [
            'labels' => [
                'name' => __('Présentations de produits', THEME_TEXT_DOMAIN),
                'singular_name' => __('Présentation du produit', THEME_TEXT_DOMAIN)
            ],
            'capability_type' => $this->_type,
            'public' => true,
            'has_archive' => true,
            'hierarchical' => $this->_type == 'page',
            'menu_icon' => 'dashicons-admin-' . $this->_type,
            'supports' => [
                'title', 'editor', 'excerpt'
            ]
        ];
    }
}