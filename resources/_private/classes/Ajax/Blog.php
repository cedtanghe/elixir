<?php

namespace Isatech\Ajax;

class Blog 
{
    public function __construct()
    {
        add_action('wp_ajax_nopriv_get_blog_article', [$this, 'getBlogArticle']);
        add_action('wp_ajax_get_blog_article', [$this, 'getBlogArticle']);
    }
    
    public function getBlogArticle()
    {
        $result = ['status' => 'error', 'HTML' => ''];

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 135;
        $post = Isatech\Page\Blog::getPost($id);

        if(null !== $post) 
        {
            $result['status'] = 'success';
            $result['data']['HTML'] = $post;
        }

        wp_send_json($result);
    }
}