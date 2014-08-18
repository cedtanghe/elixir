<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-tabs');
wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css');
?>

<?php echo $this->getItem('subtitle')->render(); ?>

<label>Vidéo ou image :</label>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Image</a></li>
        <li><a href="#tabs-2">Video</a></li>
    </ul>
    
    <?php
    $value = $this->getItem('choice_media')->getValue();
    $data = $this->getItem('choice_media')->getData();
    $keys = array_keys($data);
    $values = array_values($data);
    ?>
    
    <div id="tabs-1">
        <?php echo $this->getItem('image')->render(); ?>
        
        <label><input type="radio" name="choice_media" value="<?php echo $keys[0]; ?>" <?php if($keys[0] == $value) echo 'checked'; ?>/><?php echo $values[0]; ?></label>
    </div>
    <div id="tabs-2">
        <?php echo $this->getItem('video')->render(); ?>
        
        <label><input type="radio" name="choice_media" value="<?php echo $keys[1]; ?>" <?php if($keys[1] == $value) echo 'checked'; ?>/><?php echo $values[0]; ?></label>
    </div>
</div>

<script type="text/javascript">
    (function($) 
    {
        $(document).ready(function() 
        {
            $('#tabs').tabs();
        });
    })(jQuery);
</script>