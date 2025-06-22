# Krayin Google Integration with Gmail Support

## 📧 Professional Gmail Integration for Krayin CRM

Krayin Google Integration with complete Gmail support that allows your business to scale efficiently while bypassing SMTP port restrictions.

### ✨ **Key Features:**

**🗓️ Google Calendar Integration:**
- Connect to Google accounts
- Fetch events from selected calendars
- Two-way synchronization for events
- Real-time event synchronization
- Create Google Meet links directly from activity forms

**📧 Gmail Integration (NEW):**
- **Send emails via Gmail API** - Bypass SMTP port restrictions completely
- **Use existing CRM email features** - All templates, notifications work automatically
- **Better deliverability** - Gmail's reliable email infrastructure
- **Easy setup** - Just change your mail driver configuration

---

## � **Requirements**

- **Krayin CRM**: v2.0.0 or higher
- **PHP**: 8.1 or higher
- **Google Cloud Project** with enabled APIs

---

## 🚀 **Installation**

### 1. Install Package

```bash
composer require sergiotijero/krayin-google-integration-with-email
```

### 2. Run Migration

```bash
php artisan migrate
```

### 3. Publish Assets

```bash
php artisan vendor:publish --force
# Select GoogleServiceProvider when prompted
```

### 4. Clear Cache

```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

---

## ⚙️ **Configuration**

### 1. Google Cloud Console Setup

1. **Create/Select Project** in [Google Cloud Console](https://console.cloud.google.com/)
2. **Enable APIs:**
   - Google Calendar API
   - Gmail API (for email integration)
3. **Create OAuth 2.0 Credentials:**
   - Application type: Web application
   - Add your domain to authorized domains
   - Download credentials JSON

### 2. Environment Configuration

Add to your `.env` file:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/admin/google/oauth

# Gmail Integration (Optional)
MAIL_MAILER=gmail
MAIL_USERNAME=your-gmail-account@gmail.com
```

### 3. Google Configuration File

Update `config/google.php` (created after publishing):

```php
<?php

return [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri'  => env('GOOGLE_REDIRECT_URL'),
    
    'scopes' => [
        // Calendar permissions
        \Google_Service_Calendar::CALENDAR,
        
        // Gmail permissions (for email integration)
        \Google_Service_Gmail::GMAIL_SEND,
        \Google_Service_Gmail::GMAIL_COMPOSE,
    ],
    
    'approval_prompt' => 'force',
    'access_type'     => 'offline',
    'include_granted_scopes' => true,
];
```

### 4. Additional Configuration Files

**Update `routes/breadcrumbs.php`:**

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

**Update `config/krayin-vite.php`:**

```php
<?php

return [
    'viters' => [
        // ...existing viters...

        'google' => [
            'hot_file'                 => 'google-vite.hot',
            'build_directory'          => 'google/build',
            'package_assets_directory' => 'src/Resources/assets',
        ],
    ],
];
```

**Update `app/Http/Middleware/VerifyCsrfToken.php`:**

```php
protected $except = [
    // ...existing exceptions...
    'admin/google/webhook',
];
```

**Update `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    // ...existing schedules...
    
    // Google Calendar sync
    $schedule->job(new \Webkul\Google\Jobs\PeriodicSynchronizations())->everyFifteenMinutes();
    $schedule->job(new \Webkul\Google\Jobs\RefreshWebhookSynchronizations())->daily();
}
```

---

## 📧 **Gmail Integration Setup**

### 🚀 **Quick Setup (3 Steps):**

#### Step 1: Configure Google Account
1. Go to **Admin → Google Integration**
2. Connect your Google account with Gmail permissions
3. Enable Gmail for your account

#### Step 2: Update .env Configuration
```env
# Change your mail configuration to use Gmail
MAIL_MAILER=gmail
MAIL_USERNAME=your-gmail-account@gmail.com
```

#### Step 3: Test Configuration
- Go to **Admin → Google → Gmail Configuration**
- Send a test email to verify everything works

### 🎯 **How Gmail Integration Works:**

- **Seamless Integration**: Uses Laravel's native mail system
- **No Code Changes**: All existing email templates and notifications work automatically
- **Gmail API**: Sends emails through Gmail API instead of SMTP
- **Smart Account Selection**: Automatically uses the configured Gmail account

### 📧 **Benefits:**

- **No SMTP Issues**: Completely bypasses server SMTP port restrictions
- **Better Deliverability**: Gmail's servers ensure better email delivery
- **Existing Features**: All CRM email features work automatically
- **Easy Setup**: Just change your .env file and you're done!

### 📋 **Troubleshooting:**

- If emails don't send, check Gmail permissions in Google Integration
- Verify your MAIL_USERNAME matches your connected Google account
- Use the test email feature to verify configuration
- Ensure Gmail API is enabled in Google Cloud Console

---

## 🗓️ **Calendar Integration Usage**

### 1. Connect Google Account
- Navigate to **Admin → Google Integration**
- Click "Connect Google Account"
- Authorize the required permissions

### 2. Sync Calendars
- Select calendars to sync
- Configure sync settings
- Enable real-time synchronization

### 3. Create Events
- Use Krayin activities
- Events sync automatically to Google Calendar
- Google Meet links can be generated automatically

---

## 🔧 **Advanced Configuration**

### Webhook Configuration
For real-time synchronization, configure webhooks:

```php
// The package automatically handles webhook endpoints
// Webhook URL: https://yourdomain.com/admin/google/webhook
```

### Customizing Sync Intervals
Modify sync schedules in `app/Console/Kernel.php`:

```php
// Sync every 5 minutes (more frequent)
$schedule->job(new \Webkul\Google\Jobs\PeriodicSynchronizations())->everyFiveMinutes();

// Sync hourly (less frequent)
$schedule->job(new \Webkul\Google\Jobs\PeriodicSynchronizations())->hourly();
```

---

## 🎯 **Features Overview**

### ✅ **What's Included:**
- Google OAuth 2.0 authentication
- Calendar two-way synchronization
- Gmail API email transport
- Google Meet integration
- Real-time webhook synchronization
- Admin panel configuration interface
- Multi-account support
- Automatic token refresh

### 🚧 **Future Enhancements:**
- Gmail email reading/syncing
- Advanced email management
- Contact synchronization
- Drive integration

---

## 🔒 **Security**

- Uses OAuth 2.0 for secure authentication
- Tokens are encrypted and stored securely
- Automatic token refresh handling
- No plain-text password storage
- CSRF protection for webhooks

---

## 🐛 **Troubleshooting**

### Common Issues:

1. **OAuth Error**: Verify client ID, secret, and redirect URL
2. **Permission Denied**: Check Google Cloud Console API enablement
3. **Sync Issues**: Verify webhook URL and CSRF exception
4. **Gmail Not Working**: Ensure Gmail API is enabled and account has permissions

### Debug Mode:
Enable debug logging in `.env`:
```env
LOG_LEVEL=debug
```

---

## 📄 **License**

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 **Authors**

- **Jitendra Singh** - Original Calendar Integration
- **Sergio Tijero** - Gmail Integration & Enhancements

---

> **Ready to go!** Your Krayin CRM now has powerful Google integration with Gmail API email transport. All your existing email functionality works seamlessly through Gmail's reliable infrastructure.