### 1. Introduction:

Krayin Google Integration with Email Support.

It packs in lots of demanding features that allows your business to scale in no time:

* Admin user can connect to their google account.
* User can fetch all events from selected calendars
* Support two-way synchronization for events
* Support real time event synchronization
* User can create google meet link directly from activity form
* **NEW: Complete Gmail integration for email management**
* **NEW: Send emails via Gmail API (bypass SMTP restrictions)**
* **NEW: Receive and manage emails directly in Krayin CRM**
* **NEW: Two-way email synchronization with Gmail**
* **NEW: Email attachment support**


### 2. Requirements:

* **Krayin**: v2.0.0 or higher.


### 3. Installation:

* Go to the root folder of **Krayin** and run the following command

~~~php
composer require krayin/krayin-google-integration
~~~

* Run these commands below to complete the setup

~~~
php artisan migrate
~~~

~~~
php artisan route:cache
~~~

~~~
php artisan vendor:publish --force

-> Search GoogleServiceProvider navigate to it and then press enter to publish all assets and configurations.
~~~


### 4. Configuration:

* Goto **routes/breadcrumbs.php** file and add following lines

```php
Breadcrumbs::for('google.calendar.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.calendar.index.title'), route('admin.google.index', ['route' => request('route')]));
});

Breadcrumbs::for('google.meet.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.meet.index.title'), route('admin.google.index', ['route' => request('route')]));
});
```

* Goto **config/krayin-vite.php** file and add following lines

```php
<?php

return [
    'viters' => [
        // ...

        'google' => [
            'hot_file'                 => 'google-vite.hot',
            'build_directory'          => 'google/build',
            'package_assets_directory' => 'src/Resources/assets',
        ],
    ],
];

```

* Goto **.env** file and add following lines

```.env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/google/oauth"
GOOGLE_WEBHOOK_URI="${APP_URL}/google/webhook"
```

* Goto **config/services.php** file and add following lines

```php
return [
    // ...
    
    'google' => [
        // Our Google API credentials.
        'client_id'       => env('GOOGLE_CLIENT_ID'),
        'client_secret'   => env('GOOGLE_CLIENT_SECRET'),
        
        // The URL to redirect to after the OAuth process.
        'redirect_uri'    => env('GOOGLE_REDIRECT_URI'),
        
        // The URL that listens to Google webhook notifications (Part 3).
        'webhook_uri'     => env('GOOGLE_WEBHOOK_URI'),
        
        // Let the user know what we will be using from his Google account.
        'scopes'          => [
            // Getting access to the user's email.
            \Google_Service_Oauth2::USERINFO_EMAIL,
            
            // Managing the user's calendars and events.
            \Google_Service_Calendar::CALENDAR,
            
            // Gmail API scopes for email management.
            \Google_Service_Gmail::GMAIL_READONLY,  // Read emails
            \Google_Service_Gmail::GMAIL_SEND,      // Send emails
            \Google_Service_Gmail::GMAIL_COMPOSE,   // Compose emails
            \Google_Service_Gmail::GMAIL_MODIFY,    // Modify email labels/status
        ],
        
        // Enables automatic token refresh.
        'approval_prompt' => 'force',
        'access_type'     => 'offline',
        
        // Enables incremental scopes (useful if in the future we need access to another type of data).
        'include_granted_scopes' => true,
    ],
];
```

* Goto **app/Http/Middleware/VerifyCsrfToken.php** file and add following line under $except array

```php
protected $except = [
    // ...
    'google/webhook',
];
```

* Goto **app/Console/Kernel.php** file and update the schedule function with the following lines

```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new \Webkul\Google\Jobs\PeriodicSynchronizations())->everyFifteenMinutes();
    $schedule->job(new \Webkul\Google\Jobs\RefreshWebhookSynchronizations())->daily();
    
    // Gmail synchronization - sync emails every 5 minutes for active accounts
    $schedule->job(new \Webkul\Google\Jobs\PeriodicGmailSync())->everyFiveMinutes();
}
```

### 5. Clear Cache:
~~~
php artisan cache:clear

php artisan config:cache
~~~

### 6. Gmail Setup:

The integration now includes full Gmail support! This allows you to:

- **Send emails via Gmail API**: Bypass SMTP port restrictions by using Gmail's API
- **Receive emails**: Sync emails from Gmail directly into Krayin
- **Manage emails**: Read, reply, forward, and organize emails from within Krayin
- **Attachment support**: Download and manage email attachments
- **Two-way sync**: Changes made in Gmail are reflected in Krayin and vice versa

**Gmail Features Available:**
- Compose and send emails
- Reply to emails
- Forward emails
- Mark emails as read/unread
- Organize emails by labels (Inbox, Sent, Drafts, Trash, etc.)
- Search emails
- Download attachments
- Real-time email synchronization

**Gmail API Setup:**
1. In your Google Cloud Console, make sure to enable the Gmail API
2. Update your OAuth 2.0 scopes to include Gmail permissions
3. The integration will automatically sync emails every 5 minutes (configurable)

**Accessing Gmail:**
- Navigate to Admin > Google > Gmail
- Or use the Gmail menu item in the admin panel
- First-time setup will require re-authenticating with Google to grant Gmail permissions


> That's it, now just execute the project on your specified domain.
