<?php

namespace Elixir\Helper;

use Elixir\Filter\Escaper;
use Elixir\Form\Field\FieldInterface;
use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Form
{
    /**
     * @var Escaper 
     */
    protected $_escaper;
    
    /**
     * @var boolean
     */
    protected $_protection = true;
    
    /**
     * @param Escaper $pEscaper
     */
    public function __construct(Escaper $pEscaper = null)
    {
        $this->setEscaper($pEscaper);
    }
    
    /**
     * @param Escaper $pValue
     */
    public function setEscaper(Escaper $pValue = null)
    {
        $this->_escaper = $pValue;
    }
    
    /**
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setEnabledProtection($pValue)
    {
        $this->_protection = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isEnableProtection()
    {
        return !$this->_protection;
    }

    /**
     * @param FormInterface $pForm
     * @param boolean $pUseLabel
     * @param boolean $pUseError
     * @param string $pSeparator
     * @return string
     */
    public function form(FormInterface $pForm, $pUseLabel = true, $pUseError = true, $pSeparator = "\n")
    {
        $pForm->prepare();
        
        $result = '';
        
        foreach($pForm->gets() as $field)
        {
            $result .= $this->field($field, $pUseLabel, $pUseError) . $pSeparator;
        }
        
        if(null !== $pForm->getParent())
        {
            return $this->openFormTag($pForm) . $result . $this->closeFormTag();
        }
        
        return $result;
    }
    
    /**
     * @param FormInterface $pForm
     * @param boolean $pUseLabel
     * @param boolean $pUseError
     * @param string $pSeparator
     * @return string
     */
    public function formRest(FormInterface $pForm, $pUseLabel = true, $pUseError = true, $pSeparator = "\n")
    {
        $result = '';
        
        foreach($pForm->gets() as $item)
        {
            if(!$item->isPrepared())
            {
                $result .= $this->field($item, $pUseLabel, $pUseError) . $pSeparator;
            }
        }
        
        return $result;
    }

    /**
     * @param FormInterface $pForm
     * @param array $pAttributes
     * @return string
     */
    public function openFormTag(FormInterface $pForm, array $pAttributes = [])
    {
        $attributes = array_merge($pForm->getAttributes(), $pAttributes);
        
        if(!isset($attributes['name']) && isset($attributes['id']))
        {
            $attributes['name'] = $attributes['id'];
        }
        
        return sprintf('<form%s>', $this->HTMLAtributes($attributes));
    }
    
    /**
     * @return string
     */
    public function closeFormTag()
    {
        return '</form>';
    }
    
    /**
     * @param string $pLegend
     * @param array $pAttributes
     * @return string
     */
    public function openFieldsetTag($pLegend = null, array $pAttributes = [])
    {
        $result = sprintf('<fieldset%s>', $this->HTMLAtributes($pAttributes));
        
        if(null !== $pLegend)
        {
            $result .= sprintf('<legend>%s</legend>', $pLegend);
        }
        
        return $result;
    }
    
    /**
     * @return string
     */
    public function closeFieldsetTag()
    {
        return '</fieldset>';
    }

    /**
     * @param array $pAttributes
     * @return string
     */
    public function openLabelTag(array $pAttributes = [])
    {
        return sprintf('<label%s>', $this->HTMLAtributes($pAttributes));
    }
    
    /**
     * @return string
     */
    public function closeLabelTag()
    {
        return '</label>';
    }
    
    /**
     * @param FieldInterface $pField
     * @param boolean $pUseLabel
     * @param boolean $pUseErr
     * @return string
     * @throws \RuntimeException
     */
    public function field(FieldInterface $pField, $pUseLabel = true, $pUseError = true)
    {
        $render = null;
        
        if(method_exists($this, $pField->getHelper()))
        {
            $render = 'inner';
        }
        else if(is_callable($pField->getHelper()))
        {
            $render = 'outer';
        }
        
        if(null === $render)
        {
            throw new \RuntimeException(sprintf('There is no rendering method for this item "%s".', $pField->getName()));
        }
        
        $result = '';
        
        if($pUseLabel && null !== $pField->getLabel())
        {
            $attributes = $pField->getAttributes();
            $labelAttributes = isset($attributes['label']) ? (array)$attributes['label'] : [];
            
            $result .= $this->openLabelTag(
                array_merge(
                    ['class' => 'form-label', 'for' => $pField->getName()],
                    $labelAttributes
                )
            );
            
            $result .= $pField->getLabel();
            $result .= $this->closeLabelTag();
        }
        
        if($render == 'inner')
        {
            $result .= $this->{$pField->getHelper()}($pField);
        }
        else
        {
            $result .= call_user_func_array($pField->getHelper(), [$pField]);
        }
        
        if($pUseError && $pField->hasError())
        {
            $result .= $this->fieldErrors($pField);
        }
        
        return $result;
    }
    
    /**
     * @param FieldInterface $pField
     * @param string $pTag
     * @param array $pAttributes
     * @return string
     */
    public function fieldErrors(FieldInterface $pField, $pTag = 'ul', array $pAttributes = ['class' => 'form-error'])
    {
        $result = '';
        
        if($pField->hasError())
        {
            if(in_array(strtolower($pTag), ['ul', 'ol']))
            {
                $result = sprintf('<%s%s>', $pTag, $this->HTMLAtributes($pAttributes));

                foreach((array)$pField->errors() as $error)
                {
                    if(empty($error))
                    {
                        continue;
                    }

                    $result .= sprintf('<li>%s</li>', $error);
                }

                $result .= sprintf('</%s>', $pTag);
            }
            else
            {
                $result = '';

                foreach((array)$pField->errors() as $error)
                {
                    if(empty($error))
                    {
                        continue;
                    }

                    $result .= sprintf(
                        '<%s%s>%s</%s>', 
                        $pTag, 
                        $this->HTMLAtributes($pAttributes), 
                        $error, 
                        $pTag
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @return string
     */
    public function input(FieldInterface $pField, array $pAttributes = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        if(!isset($attributes['type']))
        {
            $attributes['type'] = 'text';
        }
        
        return sprintf(
            '<input%s/>', 
            $this->HTMLAtributes(
                array_merge(
                    $attributes, 
                    ['value' => $pField->getValue(false)]
                )
            )
        );
    }
    
    /**
     * @param FieldInterface $pField
     * @return string
     */
    public function CSRF(FieldInterface $pField)
    {
        return $this->input($pField);
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @return string
     */
    public function button(FieldInterface $pField, array $pAttributes = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        return sprintf(
            '<button%s>%s</button>', 
            $this->HTMLAtributes($attributes), 
            $this->HTML($pField->getValue(false))
        );
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @return string
     */
    public function textarea(FieldInterface $pField, array $pAttributes = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        return sprintf(
            '<textarea%s>%s</textarea>', 
            $this->HTMLAtributes($attributes), 
            $this->HTML($pField->getValue(false))
        );
    }
    
    /**
     * @param FieldInterface $pField
     * @return string
     */
    public function select(FieldInterface $pField, array $pAttributes = [], array $pOptions = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        $options = array_merge($pField->getOptions(), $pOptions);
        
        $data = $options['data'];
        $dataUseKeys = $options['data-use-keys'];
        
        if(isset($attributes['multiple']) && $attributes['multiple'])
        {
            if(substr($attributes['name'], -2) != '[]')
            {
                $attributes['name'] .= '[]';
            }
        }
        
        $list = $this->createOptions($pField->getValue(false), $data, $dataUseKeys);
        
        if(isset($attributes['placeholder']))
        {
            $list = sprintf(
                '<option value="" %s disabled style="display:none;">%s</option>%s', 
                strpos($list, 'selected') ? '' : 'selected',
                $attributes['placeholder'],
                $list
            );
            
            unset($attributes['placeholder']);
        }
        
        return sprintf(
            '<select%s>%s</select>',
            $this->HTMLAtributes($attributes),
            $list
        );
    }
    
    /**
     * @param mixed $pValue
     * @param array $pData
     * @param boolean $pDataUseKeys
     * @return string
     */
    protected function createOptions($pValue, $pData, $pDataUseKeys)
    {
        $result = '';
        
        foreach($pData as $key => $value)
        {
            if(is_array($value))
            {
                $result .= sprintf(
                    '<optgroup%s>%s</optgroup>',
                    $this->HTMLAtributes(['label' => $key]),
                    $this->createOptions($pValue, $value, $pDataUseKeys)
                );
                
                continue;
            }
            
            $a = [];
            
            if($pDataUseKeys)
            {
                $a['value'] = $key;
                
                if($key == $pValue)
                {
                    $a['selected'] = '';
                }
            }
            else if($value == $pValue)
            {
                $a['selected'] = '';
            }
            
            $result .= sprintf(
                '<option%s>%s</option>', 
                $this->HTMLAtributes($a),
                $value
            );
        }
        
        return $result;
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @param array $pOptions
     * @return string
     */
    public function checkbox(FieldInterface $pField, array $pAttributes = [], array $pOptions = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        $options = array_merge($pField->getOptions(), $pOptions);
        
        $placement = isset($options['placement']) ? $options['placement'] : 'inner';
        if(!in_array($placement, ['inner', 'before', 'after']))
        {
            $placement = 'inner';
        }
        
        $data = $options['data'];
        $dataUseKeys = $options['data-use-keys'];
        $separator = isset($options['separator']) ? $options['separator'] : '';
        
        if(count($data) > 1)
        {
            if(substr($attributes['name'], -2) != '[]')
            {
                $attributes['name'] .= '[]';
            }
        }
        
        $result = '';
        $count = count($data);
        $increment = $count > 1;
        $authorizeBoolean = $count == 1;
        $c = 0;
        
        foreach($data as $key => $value)
        {
            $a = array_slice($attributes, 0);
            $id = isset($a['id']) ? $a['id'] : $count > 1 ? substr($a['name'], 0, -2) : $a['name'];
            
            if($increment)
            {
                $a['id'] = $id . '-' . ($c + 1);
            }
            
            $a['value'] = $dataUseKeys ? $key : $value;
            
            foreach((array)$pField->getValue(false) as $v)
            {
                if($authorizeBoolean)
                {
                    if(null === $v)
                    {
                        $v = false;
                    }

                    if($v)
                    {
                        $a['checked'] = '';
                        break;
                    }
                }
                else if($a['value'] == $v)
                {
                    $a['checked'] = '';
                    break;
                }
            }
            
            $input = sprintf('<input%s/>', $this->HTMLAtributes($a));
            
            if($count > 1 || $dataUseKeys)
            {
                $labelAttributes = array_merge(
                    ['class' => 'checkbox-label'],
                    isset($attributes['label']) ? (array)$attributes['label'] : []
                );
                
                if(isset($a['id']))
                {
                    $labelAttributes['for'] = $a['id'];
                }
                
                switch($placement)
                {
                    case 'inner':
                        $input = sprintf(
                            '<label%s>%s%s</label>', 
                            $this->HTMLAtributes($labelAttributes), 
                            $input, 
                            $value
                        );
                    break;
                    case 'before':
                        $input = sprintf(
                            '<label%s>%s</label>%s', 
                            $this->HTMLAtributes($labelAttributes), 
                            $value,
                            $input
                        );
                    break;
                    case 'after':
                        $input = sprintf(
                            '%s<label%s>%s</label>', 
                            $input,
                            $this->HTMLAtributes($labelAttributes), 
                            $value
                        );
                    break;
                }
            }
            
            if(isset($options['wrap']))
            {
                $input = sprintf($options['wrap'], $input);
            }
            
            $result .= $input;
            $result .= $c == $count - 1 ? '' : $separator;
            
            ++$c;
        }
        
        return $result;
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @param array $pOptions
     * @return string
     */
    public function radio(FieldInterface $pField, array $pAttributes = [], array $pOptions = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        $options = array_merge($pField->getOptions(), $pOptions);
        
        $placement = isset($options['placement']) ? $options['placement'] : 'inner';
        
        if(!in_array($placement, ['inner', 'before', 'after']))
        {
            $placement = 'inner';
        }
        
        $data = $options['data'];
        $dataUseKeys = $options['data-use-keys'];
        $separator = isset($options['separator']) ? $options['separator'] : '';
        
        $result = '';
        $count = count($data);
        $increment = $count > 1;
        $c = 0;
        
        foreach($data as $key => $value)
        {
            $a = array_slice($attributes, 0);
            $id = isset($a['id']) ? $a['id'] : $a['name'];
            
            if($increment)
            {
                $a['id'] = $id . '-' . ($c + 1);
            }
            
            $a['value'] = $dataUseKeys ? $key : $value;
            
            if($a['value'] == $pField->getValue(false))
            {
                $a['checked'] = '';
            }
            
            $input = sprintf('<input%s/>', $this->HTMLAtributes($a));
            
            if($count > 1 || $dataUseKeys)
            {
                $labelAttributes = array_merge(
                    ['class' => 'radio-label'], 
                    isset($attributes['label']) ? (array)$attributes['label'] : []
                );
                
                if(isset($a['id']))
                {
                    $labelAttributes['for'] = $a['id'];
                }
                
                switch($placement)
                {
                    case 'inner':
                        $input = sprintf(
                            '<label%s>%s%s</label>', 
                            $this->HTMLAtributes($labelAttributes), 
                            $input, 
                            $value
                        );
                    break;
                    case 'before':
                        $input = sprintf(
                            '<label%s>%s</label>%s', 
                            $this->HTMLAtributes($labelAttributes), 
                            $value,
                            $input
                        );
                    break;
                    case 'after':
                        $input = sprintf(
                            '%s<label%s>%s</label>', 
                            $input,
                            $this->HTMLAtributes($labelAttributes), 
                            $value
                        );
                    break;
                }
            }
            
            if(isset($options['wrap']))
            {
                $input = sprintf($options['wrap'], $input);
            }
            
            $result .= $input;
            $result .= $c == $count - 1 ? '' : $separator;
            
            ++$c;
        }
        
        return $result;
    }
    
    /**
     * @param FieldInterface $pField
     * @param array $pAttributes
     * @param array $pOptions
     * @return string
     */
    public function file(FieldInterface $pField, array $pAttributes = [], array $pOptions = [])
    {
        $pField->prepare();
        $attributes = array_merge($pField->getAttributes(), $pAttributes);
        
        if(isset($attributes['value']) && null === $pField->getValue())
        {
            $pField->setValue($attributes['value'], false);
            unset($attributes['value']);
        }
        
        $options = array_merge($pField->getOptions(), $pOptions);
        
        $result = '';
        
        if(isset($options['APC_UPLOAD_PROGRESS_DATA']))
        {
            $data = $options['APC_UPLOAD_PROGRESS_DATA'];
            $result .= sprintf(
                '<input%s/>', 
                $this->HTMLAtributes(
                    [
                        'name' => 'APC_UPLOAD_PROGRESS',
                        'type' => 'hidden',
                        'id' => $data['id'],
                        'value' => $data['value']
                    ]
                )
            );
        }
        
        if(isset($options['MAX_FILE_SIZE']))
        {
            $result .= sprintf(
                '<input%s/>', 
                $this->HTMLAtributes(
                    [
                        'name' => 'MAX_FILE_SIZE',
                        'type' => 'hidden',
                        'value' => $options['MAX_FILE_SIZE']
                    ]
                )
            );
        }
        
        $result .= sprintf('<input%s/>', $this->HTMLAtributes($attributes));
        return $result;
    }
    
    /**
     * @param array $pAttributes
     * @return string
     */
    protected function HTMLAtributes(array $pAttributes)
    {
        $result = '';
        
        foreach($pAttributes as $key => $value)
        {
            if(is_array($value))
            {
                continue;
            }

            if(null !== $this->_escaper && $this->_protection)
            {
                $value = $this->_escaper->escapeHTMLAttr($value);
            }

            $result .= sprintf(' %s="%s"', $key, $value);
        }
        
        return $result;
    }
    
    /**
     * @param string $pStr
     * @return string
     */
    protected function HTML($pStr)
    {
        if(null !== $this->_escaper && $this->_protection)
        {
            return $this->_escaper->escapeHTML($pStr);
        }
        
        return $pStr;
    }
    
    /**
     * @see Form::form()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array([$this, 'form'], $args);
    }
}