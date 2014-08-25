<?php

return [
    '{MODULE}-INDEX' => [
        'regex' => \Elixir\Util\Str::slug(\Elixir\Util\Str::snake('{MODULE}')),
        'parameters' => ['_mvc' => '(@{MODULE})::index::index']
    ]
];
