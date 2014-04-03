<?php

return array(
    '{MODULE}-INDEX' => array(
        'regex' => \Elixir\Util\Str::slug(\Elixir\Util\Str::snake('{MODULE}')),
        'parameters' => array('_mvc' => '(@{MODULE})::index::index')
    )
);