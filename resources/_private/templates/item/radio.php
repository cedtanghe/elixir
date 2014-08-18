<div class="form-group">
    <?php if(null !== $this->_label): ?>
    <label><?php _e($this->_label); ?> :<?php if($this->_required) echo ' *' ?></label>
    <?php endif; ?>
    
    <?php
    $attributes = array_slice($this->_attributes, 0);
    
    if(!isset($attributes['id']))
    {
        $attributes = $attributes['name'];
    }
    
    $id = $attributes['id'];
    unset($attributes['id']);
    
    $increment = count($this->_data) > 0;
    $c = 0;
    
    foreach($this->_data as $key => $value)
    {
        $attrs = ' id = "' . esc_attr($id . ($increment ? '-' . ++$c : '')) . '"';
        
        foreach($attributes as $key => $value)
        {
            $attrs .= ' ' . $key . ' = "' . esc_attr($value) . '"';
        }
        
        if($this->getValue() == $key)
        {
            $attrs .= ' checked';
        }
        
        ?>
        <label class="label-radio">
            <input<?php echo $attrs; ?> value="<?php echo esc_attr($key); ?>"/>
            <?php echo esc_html($value); ?>
        </label>
        <?php
    }
    ?>
</div>