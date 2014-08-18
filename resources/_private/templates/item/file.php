<div class="form-group">
    <?php if(null !== $this->_label): ?>
    <label><?php _e($this->_label); ?> :<?php if($this->_required) echo ' *' ?></label>
    <?php endif; ?>
    
    <?php
    $attributes = array_slice($this->_attributes, 0);
    
    if(!isset($attributes['id']))
    {
        $attributes['id'] = $attributes['name'];
    }
    
    $attrs = '';
    
    foreach($attributes as $key => $value)
    {
        $attrs .= ' ' . $key . ' = "' . esc_attr($value) . '"';
    }
    ?>
    
    <input<?php echo $attrs; ?>/>
    
    <?php 
    $image = $this->getValue(); 
    $uploadDir = wp_upload_dir();
    $base = $uploadDir['baseurl'];
    ?>
    
    <?php if(!empty($image)): ?>
    <div>
        <img width="200" src="<?php echo $base . '/' . $image; ?>" />
    </div>
    <?php endif; ?>
</div>