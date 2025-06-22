<?php

return [
    'title'           => 'Google',
    'account-deleted' => 'Account removed successfully.',
    'account-synced'  => 'Account synced successfully.',
    'view'            => 'View',
    'sync'            => 'Sync',

    'activity' => [
        'google-meet'         => 'Google Meet',
        'connect-google-meet' => 'Connect Google Meet',
        'join-google-meet'    => 'Join Google Meet',
        'remove-google-meet'  => 'Remove Google Meet',
    ],

    'calendar' => [
        'index' => [
            'title'           => 'Google Calendar',
            'info'            => 'Google time management and scheduling calendar for enhancing work speed',
            'remove'          => 'Remove',
            'synced-account'  => 'Synced Account',
            'select-calendar' => 'Select the calendar you want to sync',
            'save-and-sync'   => 'Save and Sync',
            'connect'         => 'Connect Google Calendar',
        ],
    ],

    'meet' => [
        'index' => [
            'link-shared'     => '──────────<br/><br/>You are invited to join Google Meet meeting.<br/><br/>Join the Google Meet meeting: <a href=":link" target="_blank" class="text-brandColor">:link</a><br/><br/>──────────',
            'title'           => 'Google Meet',
            'info'            => 'Google time management and scheduling meet for enhancing work speed',
            'remove'          => 'Remove',
            'synced-account'  => 'Synced Account',
            'select-meet'     => 'Select the meet you want to sync',
            'save-and-sync'   => 'Save and Sync',
            'connect'         => 'Connect Google Meet',
        ],
    ],

    'tabs' => [
        'calendar' => 'Google Calendar',
        'meet'     => 'Google Meet',
        'gmail'    => 'Gmail',
    ],

    'gmail' => [
        'title' => 'Gmail Integration',
        'description' => 'Configure Gmail API integration to send emails directly through your Google account.',
        'configuration' => 'Gmail Configuration',
        'enable' => 'Enable Gmail',
        'disable' => 'Disable Gmail',
        'test-email' => 'Send Test Email',
        'test-email-placeholder' => 'Enter test email address',
        'no-accounts' => 'No Google accounts found. Please connect a Google account first.',
        'connect-account' => 'Connect Google Account',
        'reauth-required' => 'Gmail permissions missing. Please reconnect your account.',
        'reconnect' => 'Reconnect',
        'status' => [
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'permissions-granted' => 'Gmail Permissions',
            'permissions-missing' => 'Missing Permissions',
        ],
        'instructions' => [
            'title' => 'How to use Gmail Integration:',
            'step1' => 'Enable Gmail for your Google account above',
            'step2' => 'Configure your email templates to use Gmail as the mail driver',
            'step3' => 'Test the configuration by sending a test email',
        ],
        'enabled-successfully' => 'Gmail enabled successfully.',
        'disabled-successfully' => 'Gmail disabled successfully.',
        'test-email-sent' => 'Test email sent successfully.',
        'test-email-failed' => 'Failed to send test email',
        'insufficient-permissions' => 'This account does not have sufficient Gmail permissions.',
        'not-configured' => 'Gmail is not properly configured for this account.',
    ],
];
