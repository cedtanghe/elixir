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
    ?>
    
    <?php wp_editor($this->getValue(), $attributes['id'], $this->_settings); ?> 
</div>