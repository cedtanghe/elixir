<?php

return array(
    '{MODULE}-INDEX' => array(
        'regex' => \Elixir\Util\Str::slug('{MODULE}', '-'),
        'parameters' => array('_mvc' => '(@{MODULE})::index::index')
    )
);