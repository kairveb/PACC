<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Reverse Proxies
    |--------------------------------------------------------------------------
    |
    | Set TRUSTED_PROXIES in the production environment to the exact proxy IP
    | addresses or CIDR ranges used by the hosting provider. REMOTE_ADDR is
    | appropriate for a direct connection and the local development server.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', 'REMOTE_ADDR'),

];
