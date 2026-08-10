<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactivity Auto-Logout
    |--------------------------------------------------------------------------
    |
    | Number of seconds a user may remain idle before the system signs them
    | out automatically. Defaults to 180 seconds (3 minutes) as requested.
    |
    */
    'timeout' => env('INACTIVITY_TIMEOUT', 180),

];
