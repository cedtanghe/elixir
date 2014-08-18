<?php

wp_enqueue_script('jquery.form.min', get_stylesheet_directory_uri() . '/js/vendor/jquery.form.min.js', [], '1.0', false);

add_action('post_edit_form_tag', function()
{
    echo ' enctype="multipart/form-data"';
});

$uploadDir = wp_upload_dir();
$base = $uploadDir['baseurl'];

wp_nonce_field('product_customers_metabox', 'product_customers_wpnonce');

?>

<table id="product-customers" class="widefat fixed">
    <?php foreach($this->getValue() as $customer): ?>
    <tr class="info">
        <td><img width="75" src="<?php echo $base . $customer['image']; ?>"/></td>
        <td><?php echo $customer['description']; ?></td>
        <td><?php echo $customer['url']; ?></td>
        <td align="right"><button class="customer-delete" data-customer="<?php echo $customer['image']; ?>">Supprimer</button></td>
    </tr>
    <?php endforeach; ?>
    <tr class="add-customer">
        <td><input type="file" name="customer-file"/></td>
        <td><input type="text" name="customer-description"/></td>
        <td><input type="text" name="customer-url"/></td>
        <td align="right"><button id="customer-submit">Ajouter</button></td>
    </tr>
</table>

<script type="text/javascript">
    (function($) 
    {
        $(document).ready(function() 
        {
            $('#customer-submit').on('click', function(e)
            {
                e.preventDefault();
                
                $('form[name="post"]').ajaxSubmit({
                    type:'POST',
                    url:'<?php bloginfo('url'); ?>/wp-admin/admin-ajax.php',
                    dataType:'json',
                    async:false,
                    data:{
                        action:'add_product_customer',
                    },
                    success:function(pResult)
                    {
                        if(pResult.status == 'success')
                        {
                            $('#product-customers input').clearFields();
                            
                            var alternate = $('#product-customers tr.info:last').hasClass('alternate') ? 'alternate' : '';
                            var tr = '<tr class="' + alternate + '">'; 
                            tr += '<td><img width="75" src="' + pResult.data.file + '"/></td>';
                            tr += '<td>' + pResult.data.description + '</td>';
                            tr += '<td>' + pResult.data.url + '</td>';
                            tr += '<td align="right"><button class="customer-delete" data-customer="' +  pResult.data.image + '">Supprimer</button></td>';
                            tr += '</tr>';
                            
                            $('#product-customers tr.add-customer').before(tr);
                        }
                    }
                });
            });
            
            $('#product-customers').on('click', '.customer-delete',function(e)
            {
                e.preventDefault();
                
                var $button = $(this);

                $.ajax({
                    type:'POST',
                    url:'<?php bloginfo('url'); ?>/wp-admin/admin-ajax.php',
                    dataType:'json',
                    async:false,
                    data:{
                        action:'delete_product_customer',
                        customer:$button.data('customer'),
                        post_ID:$('input[name="post_ID"]').val(),
                        product_customers_wpnonce:$('input[name="product_customers_wpnonce"]').val()
                    },
                    success:function(pResult)
                    {
                        if(pResult.status == 'success')
                        {
                            $button.parent().parent().remove();
                        }
                    }
                });
            });
        });
    })(jQuery);
</script>