<?php
$createOptions = function(array $pData, $pValue)
{
    $options = '';

    foreach($pData as $key => $value)
    {
        if(is_array($value))
        {
            $options .= sprintf(
                '<optgroup%s>%s</optgroup>',
                ' label = "' . esc_attr($key) . '"',
                $createOptions($pValue, $value)
            );

            continue;
        }
        
        $options .= sprintf(
            '<option%s%s>%s</option>', 
            ' value = "' . esc_attr($key) . '"',
            $key == $pValue ? ' selected' : '',
            $value
        );
    }

    return $options;
};
?>

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
    
    if(isset($attributes['placeholder']))
    {
        $placeholder = $attributes['placeholder'];
        unset($attributes['placeholder']);
    }
    
    $attrs = '';
    
    foreach($attributes as $key => $value)
    {
        $attrs .= ' ' . $key . ' = "' . esc_attr($value) . '"';
    }
    ?>
    
    <select<?php echo $attrs; ?>>
        <?php 
        $options = $createOptions($this->_data, $this->getValue()); 
        
        if(isset($placeholder))
        {
            $options = sprintf(
                '<option value="" %s disabled style="display:none;">%s</option>%s', 
                strpos($options, 'selected') ? '' : 'selected',
                $placeholder,
                $options
            );
        }
        
        echo $options;
        ?>
    </select>
</div>