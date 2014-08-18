<?php

namespace Isatech;

class ScreenError 
{
    protected $_identifier;
    
    public function __construct($pIdentifier) 
    {
        $this->_identifier = $pIdentifier;
    }
    
    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    public function add($pKey, $pValue, $pReset = true)
    {
        if($pReset)
        {
            $_SESSION['errors'][$this->_identifier][$pKey] = $pValue;
        }
        else 
        {
            if(isset($_SESSION['errors'][$this->_identifier][$pKey]))
            {
                if(!is_array($_SESSION['errors'][$this->_identifier][$pKey]))
                {
                    $_SESSION['errors'][$this->_identifier][$pKey] = (array)$_SESSION['errors'][$this->_identifier][$pKey];
                }
                
                $_SESSION['errors'][$this->_identifier][$pKey][] = $pValue;
            }
        }
    }
    
    public function hasErrors()
    {
        return isset($_SESSION['errors'][$this->_identifier]) && count($_SESSION['errors'][$this->_identifier]) > 0;
    }
    
    public function getErrors()
    {
        if($this->hasErrors())
        {
            return $_SESSION['errors'][$this->_identifier];
        }
        
        return [];
    }
    
    public function activate()
    {
        add_action('admin_notices', function()
        {
            if(isset($_GET['error']))
            {
                if($this->hasErrors())
                {
                    add_action(
                        'wp_head', 
                        function()
                        {
                            ?>
                            <script type="text/javascript">
                                var <?php echo strtoupper(str_replace('-', '', $this->_identifier)); ?>_VALIDATION_ERRORS = '<?php json_encode($_SESSION['errors'][$this->_postType]); ?>';
                            </script>
                            <?php
                        }
                    );
                    
                    $result = '<ul>';

                    foreach($this->getErrors() as $key => $value)
                    {
                        if(empty($value))
                        {
                            continue;
                        }
                        
                        $error = '';
                        
                        foreach((array)$value as $v)
                        {
                            $error .= sprintf('<li>%s</li>', $v);
                        }

                        $result .= sprintf('<li><strong>%s :</strong><ul>%s</ul></li>', $key, $error);
                    }

                    $result .= '</ul>';
                    
                    echo sprintf('<div class="error">%s</div>', $result);
                    unset($_SESSION['errors'][$this->_identifier]);
                }
            }
        });
    }
}