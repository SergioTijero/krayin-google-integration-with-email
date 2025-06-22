<?php

namespace Webkul\Google\Mail;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Arr;
use Webkul\Google\Mail\Transport\GmailTransport;
use Webkul\Google\Services\Google;
use Webkul\Google\Models\Account;

class GmailMailManager extends MailManager
{
    /**
     * Create an instance of the Gmail SMTP driver.
     */
    protected function createGmailDriver(array $config)
    {
        $google = app(Google::class);
        
        // Get the Google account for the user or use default
        $account = $this->getGoogleAccount($config);
        
        if (!$account) {
            throw new \Exception('No Google account configured for Gmail transport');
        }

        return new GmailTransport($google, $account);
    }

    /**
     * Get the Google account to use for sending emails.
     */
    protected function getGoogleAccount(array $config)
    {
        // Try to get account by email if specified in config
        if (!empty($config['username'])) {
            $account = Account::where('email', $config['username'])->first();
            if ($account) {
                return $account;
            }
        }

        // Fallback to the first available account with Gmail permissions
        return Account::whereNotNull('token')
            ->get()
            ->first(function ($account) {
                return $account->hasGmailPermissions();
            });
    }
}
