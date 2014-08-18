<?php

namespace Isatech\Walker;

use Walker_Nav_Menu;

class MainMenu extends Walker_Nav_Menu
{
    const DESKTOP = 'desktop';
    const MOBILE = 'mobile';
    
    protected $_reference;
    
    public function __construct($pReference)
    {
        $this->_reference = $pReference;
    }
    
    public function getReference()
    {
        return $this->_reference;
    }
            
    public function start_el(&$pOutput, $pItem, $pDepth = 0, $pArgs = array(), $pId = 0)
    {
        $indent = ($pDepth) ? str_repeat("\t", $pDepth) : '';
        
        $classNames = '';

        $classes = empty($pItem->classes) ? array() :(array) $pItem->classes;
        $classes[] = 'menu-item-' . $pItem->ID;
        
        $classNames = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $pItem, $pArgs));
        $classNames = $classNames ? ' class="' . esc_attr($classNames) . '"' : '';
        
        $pId = apply_filters('nav_menu_item_id', 'menu-item-'. $pItem->ID, $pItem, $pArgs);
        $pId = $pId ? ' id="' . esc_attr($pId) . '"' : '';

        $pOutput .= $indent . '<li' . $pId . $classNames .'>';

        $atts = [];
        $atts['title'] = !empty($pItem->attr_title) ? $pItem->attr_title : '';
        $atts['target'] = !empty($pItem->target) ? $pItem->target : '';
        $atts['rel'] = !empty($pItem->xfn) ? $pItem->xfn : '';
        $atts['href'] = !empty($pItem->url) ? $pItem->url : '';
        $atts['data-link'] = $pDepth == 0 ? $pItem->post_name : '';
        
        $atts = apply_filters('nav_menu_link_attributes', $atts, $pItem, $pArgs);
        
        $attributes = '';
        
        foreach($atts as $attr => $value) 
        {
            if(!empty($value)) 
            {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }
        
        $itemOutput = '<a'. $attributes .'>';
        
        switch($this->_reference)
        {
            case self::DESKTOP:
                $icon = in_array('menu-item-has-children', $pItem->classes) ? '<span class="sprite ico_sub_menu"></span>' : '';
                $itemOutput .= '<span class="title_link">' . apply_filters('the_title', $pItem->title, $pItem->ID) . '</span>' . $icon;
            break;
            case self::MOBILE:
                $iconBefore = '<span class="sprite ico_' . $pItem->post_name . '"></span>';
                $iconAfter = in_array('menu-item-has-children', $pItem->classes) ? '<span class="sprite ico_sub_menu"></span>' : '';
                $itemOutput .= $iconBefore . '<span class="title_link">' . apply_filters('the_title', $pItem->title, $pItem->ID) . '</span>' . $iconAfter;
            break;
        }
        
        $itemOutput .= '</a>';
        $pOutput .= apply_filters('walker_nav_menu_start_el', $itemOutput, $pItem, $pDepth, $pArgs);
    }
    
    public function start_lvl(&$pOutput, $pDepth = 0, $pArgs = []) 
    {
        $indent = str_repeat("\t", $pDepth);
        $pOutput .= "\n$indent<ul class=\"sub-menu " . 'depth-' . $pDepth . "\">\n";
    }
}
