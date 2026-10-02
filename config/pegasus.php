<?php

return [
    'demo_enabled' => env('PEGASUS_DEMO_ENABLED', false),
    'owner_email' => env('PEGASUS_OWNER_EMAIL'),
    'imap_allowed_hosts' => array_filter(explode(',', env('IMAP_ALLOWED_HOSTS', 'mail.privateemail.com'))),
];
