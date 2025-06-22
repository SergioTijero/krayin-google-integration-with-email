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
        'gmail_enabled',
    ];

    protected $casts = [
        'token'         => 'json',
        'scopes'        => 'json',
        'gmail_enabled' => 'boolean',
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
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.compose',
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
     * Enable Gmail for this account.
     */
    public function enableGmail(): void
    {
        $this->update(['gmail_enabled' => true]);
    }

    /**
     * Disable Gmail for this account.
     */
    public function disableGmail(): void
    {
        $this->update(['gmail_enabled' => false]);
    }
}
