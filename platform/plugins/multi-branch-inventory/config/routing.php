<?php

return [
    'admin' => [
        'middleware' => ['web', 'core'],
        'prefix' => config('core.base.general.admin_dir', 'admin'),
        'namespace' => 'Botble\\MultiBranchInventory\\Http\\Controllers',
    ],
    
    'api' => [
        'middleware' => ['api'],
        'prefix' => 'api/v1',
        'namespace' => 'Botble\\MultiBranchInventory\\Http\\Controllers\\API',
    ],
];