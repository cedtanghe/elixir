<?php

return ['unit' => ['key-1' => 'value-1',
                             'key-2' => 'value-2'],
             'test:unit' => ['key-2' => 'new value-2',
                                  '@include' => 'include.php']];
