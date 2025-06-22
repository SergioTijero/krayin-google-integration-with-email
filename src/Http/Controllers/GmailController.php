<?php

namespace Webkul\Google\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Webkul\Google\Models\Account;

class GmailController extends Controller
{
    /**
     * Show Gmail settings page.
     */
    public function index()
    {
        $accounts = Account::whereNotNull('token')->get();
        
        return view('google::gmail.index', compact('accounts'));
    }

    /**
     * Enable Gmail for an account.
     */
    public function enable(Request $request, $accountId)
    {
        $account = Account::findOrFail($accountId);
        
        if (!$account->hasGmailPermissions()) {
            return back()->with('error', trans('google::app.gmail.insufficient-permissions'));
        }
        
        $account->enableGmail();
        
        return back()->with('success', trans('google::app.gmail.enabled-successfully'));
    }

    /**
     * Disable Gmail for an account.
     */
    public function disable(Request $request, $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->disableGmail();
        
        return back()->with('success', trans('google::app.gmail.disabled-successfully'));
    }

    /**
     * Test Gmail configuration by sending a test email.
     */
    public function test(Request $request, $accountId)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);
        
        $account = Account::findOrFail($accountId);
        
        if (!$account->gmail_enabled || !$account->hasGmailPermissions()) {
            return back()->with('error', trans('google::app.gmail.not-configured'));
        }
        
        try {
            // Configure mail to use Gmail transport for this account
            config([
                'mail.mailers.gmail' => [
                    'transport' => 'gmail',
                    'username' => $account->email,
                ],
                'mail.default' => 'gmail'
            ]);
            
            Mail::raw('This is a test email from Krayin CRM using Gmail API.', function ($message) use ($request, $account) {
                $message->to($request->test_email)
                        ->from($account->email, $account->name)
                        ->subject('Test Email from Krayin CRM - Gmail Integration');
            });
            
            return back()->with('success', trans('google::app.gmail.test-email-sent'));
            
        } catch (\Exception $e) {
            return back()->with('error', trans('google::app.gmail.test-email-failed') . ': ' . $e->getMessage());
        }
    }
}
