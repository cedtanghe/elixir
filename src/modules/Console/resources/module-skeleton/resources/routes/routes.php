<?php

use Elixir\Util\Str;

return [
    '{MODULE}-INDEX' => [
        'regex' => Str::slug(Str::snake('{MODULE}')),
        'parameters' => ['_mvc' => '(@{MODULE})::index::index']
    ]
];
