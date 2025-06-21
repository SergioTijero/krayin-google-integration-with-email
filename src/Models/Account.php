<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Google\Concerns\Synchronizable;
use Webkul\Google\Contracts\Account as AccountContract;
use Webkul\Google\Jobs\SynchronizeCalendars;
use Webkul\Google\Jobs\WatchCalendars;

class Account extends Model implements AccountContract
{
    use Synchronizable;

    protected $table = 'google_accounts';

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'token',
        'scopes',
        'gmail_last_sync_at',
    ];

    protected $casts = [
        'token'               => 'json',
        'scopes'              => 'json',
        'gmail_last_sync_at'  => 'datetime',
    ];

    /**
     * Get the user that owns the google account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the calendars.
     */
    public function calendars()
    {
        return $this->hasMany(CalendarProxy::modelClass(), 'google_account_id');
    }

    /**
     * Get the Gmail messages.
     */
    public function gmailMessages()
    {
        return $this->hasMany(GmailMessageProxy::modelClass(), 'account_id');
    }

    /**
     * Synchronize calendars.
     */
    public function synchronize()
    {
        SynchronizeCalendars::dispatch($this);
    }

    public function watch()
    {
        WatchCalendars::dispatch($this);
    }

    /**
     * Check if the account has Gmail permissions.
     */
    public function hasGmailPermissions(): bool
    {
        $requiredScopes = [
            'https://www.googleapis.com/auth/gmail.readonly',
            'https://www.googleapis.com/auth/gmail.send',
        ];

        $accountScopes = $this->scopes ?? [];

        foreach ($requiredScopes as $scope) {
            if (!in_array($scope, $accountScopes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get unread Gmail messages count.
     */
    public function getUnreadGmailCount(): int
    {
        return $this->gmailMessages()->unread()->count();
    }
}
