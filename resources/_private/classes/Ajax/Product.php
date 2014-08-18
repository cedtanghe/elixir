<?php

namespace Isatech\Ajax;

use Isatech\PostType\Item\ProductCustomers;

class Product 
{
    public function __construct()
    {
        add_action('wp_ajax_add_product_customer', [$this, 'addProductCustomer']);
        add_action('wp_ajax_delete_product_customer', [$this, 'deleteProductCustomer']);
    }
    
    public function addProductCustomer()
    {
        $result = ['status' => 'error'];

        if(!empty($_POST['product_customers_wpnonce']) && !empty($_POST['post_ID']) && !empty($_FILES['customer-file']['name']))
        {
            if(wp_verify_nonce($_POST['product_customers_wpnonce'], 'product_customers_metabox'))
            {
                $data = ProductCustomers::saveCustomer(
                    (int)$_POST['post_ID'], 
                    $_FILES['customer-file'], 
                    isset($_POST['customer-description']) ? sanitize_text_field($_POST['customer-description']) : '', 
                    isset($_POST['customer-url']) ? esc_url($_POST['customer-url']) : ''
                );

                if(false !== $data)
                {
                    $result['status'] = 'success';
                    $result['data'] = $data;
                }
            }
        }

        wp_send_json($result);
    }

    public function deleteProductCustomer()
    {
        $result = ['status' => 'error'];

        if(!empty($_POST['product_customers_wpnonce']) && !empty($_POST['post_ID']) && !empty($_POST['customer']))
        {
            if(wp_verify_nonce($_POST['product_customers_wpnonce'], 'product_customers_metabox'))
            {
                $deleted = ProductCustomers::deleteCustomer(
                    (int)$_POST['post_ID'], 
                    $_POST['customer']
                );

                if(false !== $deleted)
                {
                    $result['status'] = 'success';
                }
            }
        }

        wp_send_json($result);
    }
}