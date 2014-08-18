<?php

namespace Isatech\Page;

class Blog 
{
    public static function getItems()
    {
        // Todo
    }

    public static function getPost($pId) 
    {
        $id = $pId;
        $query = new \WP_Query('p = ' . $id);

        if ($query->have_posts()) 
        {
            while($query->have_posts()) 
            {
                $query->the_post();

                ob_start();
                include get_template_directory() . '/templates/blog/post.php';
                return ob_get_clean();
            }
        }

        wp_reset_postdata();
        
        return null;
    }
}