<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supervisor Manager Panel URL
    |--------------------------------------------------------------------------
    |
    | This value dictates the URL path where the Supervisor Manager panel will
    | be accessible. You can customize this to whatever path you prefer.
    |
    */

    'panel_url' => env('SUPERVISOR_PANEL_PATH', 'supervisor'),

    /*
    |--------------------------------------------------------------------------
    | Supervisor XML-RPC Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify the connection details for your Supervisor instance.
    | The manager connects via XML-RPC, so you must enable the [inet_http_server]
    | section in your supervisord.conf file with these credentials.
    |
    */

    'url' => env('SUPERVISOR_URL', 'http://127.0.0.1'),

    'port' => env('SUPERVISOR_PORT', 9125),

    'username' => env('SUPERVISOR_USERNAME', 'user'),

    'password' => env('SUPERVISOR_PASSWORD', '123'),

    /*
    |--------------------------------------------------------------------------
    | System Configuration Path
    |--------------------------------------------------------------------------
    |
    | This is the absolute path to the directory where the active Supervisor
    | configuration files are stored on the server (e.g., /etc/supervisor/conf.d).
    | The "Deploy" action will copy files to this location.
    |
    */

    'conf_path' => env('SUPERVISOR_CONF_PATH', '/opt/homebrew/etc/supervisor.d'),

    /*
    |--------------------------------------------------------------------------
    | Local Project Configuration Directory
    |--------------------------------------------------------------------------
    |
    | This is the directory within your Laravel project where Supervisor
    | configuration files will be managed and version controlled.
    |
    */

    'supervisors_dir' => env('SUPERVISOR_LOCAL_DIR', base_path('supervisors')),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the user model class used to authorize access to the Supervisor
    | Manager panel. Ensure this model implements `FilamentUser`.
    |
    */

    'user_model' => \App\Models\User::class,

];
