<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Files Config
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'backupDirectory' => storage_path('env-editor'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    */
    'route' => [
        'enable' => false,
        // Prefix url for route Group
        'prefix' => 'env-editor',
        // Routes base name
        'name' => 'env-editor',
        // Middleware(s) applied on route Group. Whoever reaches these routes can
        // read and rewrite the .env, so `auth` is the minimum: add your own admin
        // check too, e.g. ['web', 'auth', 'admin'] or ['web', 'auth', 'can:manage-env'].
        'middleware' => ['web', 'auth'],
        // Ability checked with the Gate before every action (e.g. 'manage-env').
        // null checks nothing beyond the middleware.
        'gate' => null,
    ],

    /* ------------------------------------------------------------------------------------------------
    |  Time Format for Views and parsed backups
    | ------------------------------------------------------------------------------------------------
    */
    'timeFormat' => 'd/m/Y H:i:s',

    /* ------------------------------------------------------------------------------------------------
     | Set Views options
     | ------------------------------------------------------------------------------------------------
     | Here you can set The "extends" blade of index.blade.php
    */
    'layout' => 'env-editor::layout',
];
