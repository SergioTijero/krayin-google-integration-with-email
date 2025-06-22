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
composer require sergiotijero/krayin-google-integration-with-email
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

### 6. Gmail Integration Setup:

This integration provides **Gmail as a Mail Transport** for Krayin CRM, allowing you to:

- **Send emails via Gmail API**: Bypass SMTP port restrictions completely
- **Use existing CRM email features**: All email templates, notifications, and functionality work automatically
- **Leverage Gmail's reliability**: Better deliverability and no server port issues

## 🚀 **Quick Setup (3 Steps):**

### Step 1: Configure Google Account
1. Go to **Admin → Google Integration**
2. Connect your Google account with Gmail permissions
3. Enable Gmail for your account

### Step 2: Update .env Configuration
```env
# Change your mail configuration to use Gmail
MAIL_MAILER=gmail
MAIL_USERNAME=your-gmail-account@gmail.com
```

### Step 3: Test Configuration
- Go to **Admin → Google → Gmail Configuration**
- Send a test email to verify everything works

## 🎯 **How It Works:**
- **Seamless Integration**: Uses Laravel's native mail system
- **No Code Changes**: All your existing email templates and notifications work
- **Gmail API**: Sends emails through Gmail API instead of SMTP
- **Smart Account Selection**: Automatically uses the configured Gmail account

## 📧 **Gmail API Setup in Google Cloud Console:**

1. **Enable Gmail API**: In Google Cloud Console, enable the Gmail API for your project
2. **OAuth 2.0 Scopes**: Make sure your OAuth application includes these scopes:
   - `https://www.googleapis.com/auth/gmail.send`
   - `https://www.googleapis.com/auth/gmail.compose`
3. **Test Domain**: Add your domain to authorized domains if needed

## 🎯 **Benefits:**
- **No SMTP Issues**: Completely bypasses server SMTP port restrictions
- **Better Deliverability**: Gmail's servers ensure better email delivery
- **Existing Features**: All CRM email features work automatically
- **Easy Setup**: Just change your .env file and you're done!

## 📋 **Troubleshooting:**
- If emails don't send, check Gmail permissions in Google Integration
- Verify your MAIL_USERNAME matches your connected Google account
- Use the test email feature to verify configuration

---

> That's it! Your Krayin CRM now sends emails through Gmail API instead of SMTP.
