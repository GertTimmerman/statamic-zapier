<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Disable Calls
    |--------------------------------------------------------------------------
    |
    | When set to true, no webhooks will be called
    |
    */

    "disable_webhooks" => env('ZAPIER_DISABLE_WEBHOOKS', false),

    /*
      |--------------------------------------------------------------------------
      | Force URL
      |--------------------------------------------------------------------------
      |
      | When set, all zapier webhooks will use this URL. This can be used to  
      | redirect all calls in a dev environment to a single test URL.
      |
      */

  "force_url" => env('ZAPIER_FORCE_URL', null),

];
