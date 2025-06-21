<?php

namespace Webkul\Google\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Webkul\Google\Repositories\GmailMessageRepository;
use Webkul\Google\Services\GmailService;

class GmailController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected GmailMessageRepository $gmailMessageRepository,
        protected GmailService $gmailService
    ) {}

    /**
     * Display a listing of Gmail messages.
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder', 'inbox');
        $account = auth()->user()->googleAccounts()->first();

        if (!$account) {
            return redirect()->route('admin.google.accounts.create')
                ->with('error', trans('google::app.gmail.no-account'));
        }

        try {
            $messages = $this->gmailService->getMessages($account, [
                'folder' => $folder,
                'maxResults' => 50
            ]);

            return view('google::gmail.index', compact('messages', 'folder', 'account'));
        } catch (\Exception $e) {
            Log::error('Gmail API Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.fetch-error'));
        }
    }

    /**
     * Show a specific Gmail message.
     */
    public function show(Request $request, $messageId)
    {
        $account = auth()->user()->googleAccounts()->first();

        if (!$account) {
            return redirect()->route('admin.google.accounts.create')
                ->with('error', trans('google::app.gmail.no-account'));
        }

        try {
            $message = $this->gmailService->getMessage($account, $messageId);

            // Mark as read if not already
            if (!$message->is_read) {
                $this->gmailService->markAsRead($account, $messageId);
                $message->is_read = true;
                $message->save();
            }

            return view('google::gmail.show', compact('message', 'account'));
        } catch (\Exception $e) {
            Log::error('Gmail Message Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.message-error'));
        }
    }

    /**
     * Compose and send a new email.
     */
    public function compose(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->send($request);
        }

        $account = auth()->user()->googleAccounts()->first();

        if (!$account) {
            return redirect()->route('admin.google.accounts.create')
                ->with('error', trans('google::app.gmail.no-account'));
        }

        return view('google::gmail.compose', compact('account'));
    }

    /**
     * Send an email via Gmail API.
     */
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
        ]);

        $account = auth()->user()->googleAccounts()->first();

        if (!$account) {
            return back()->with('error', trans('google::app.gmail.no-account'));
        }

        try {
            $emailData = [
                'to' => $this->parseEmails($request->to),
                'subject' => $request->subject,
                'body' => $request->body,
                'from' => $account->email,
            ];

            if ($request->cc) {
                $emailData['cc'] = $this->parseEmails($request->cc);
            }

            if ($request->bcc) {
                $emailData['bcc'] = $this->parseEmails($request->bcc);
            }

            $sentMessage = $this->gmailService->sendEmail($account, $emailData);

            // Store the sent message in our database
            $this->gmailService->syncMessage($account, $sentMessage->getId());

            return redirect()->route('admin.google.gmail.index', ['folder' => 'sent'])
                ->with('success', trans('google::app.gmail.sent-success'));

        } catch (\Exception $e) {
            Log::error('Gmail Send Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.send-error'))
                ->withInput();
        }
    }

    /**
     * Reply to an email.
     */
    public function reply(Request $request, $messageId)
    {
        if ($request->isMethod('post')) {
            return $this->sendReply($request, $messageId);
        }

        $account = auth()->user()->googleAccounts()->first();
        $message = $this->gmailService->getMessage($account, $messageId);

        return view('google::gmail.reply', compact('message', 'account'));
    }

    /**
     * Send a reply to an email.
     */
    public function sendReply(Request $request, $messageId)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $account = auth()->user()->googleAccounts()->first();
        $originalMessage = $this->gmailService->getMessage($account, $messageId);

        try {
            $emailData = [
                'to' => [$originalMessage->from],
                'subject' => 'Re: ' . $originalMessage->subject,
                'body' => $request->body,
                'from' => $account->email,
                'threadId' => $originalMessage->thread_id,
            ];

            $sentMessage = $this->gmailService->sendEmail($account, $emailData);

            return redirect()->route('admin.google.gmail.show', $messageId)
                ->with('success', trans('google::app.gmail.reply-success'));

        } catch (\Exception $e) {
            Log::error('Gmail Reply Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.reply-error'))
                ->withInput();
        }
    }

    /**
     * Sync Gmail messages.
     */
    public function sync(Request $request)
    {
        $account = auth()->user()->googleAccounts()->first();

        if (!$account) {
            return back()->with('error', trans('google::app.gmail.no-account'));
        }

        try {
            $this->gmailService->syncMessages($account);

            return back()->with('success', trans('google::app.gmail.sync-success'));
        } catch (\Exception $e) {
            Log::error('Gmail Sync Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.sync-error'));
        }
    }

    /**
     * Delete/trash a Gmail message.
     */
    public function delete(Request $request, $messageId)
    {
        $account = auth()->user()->googleAccounts()->first();

        try {
            $this->gmailService->deleteMessage($account, $messageId);

            return back()->with('success', trans('google::app.gmail.delete-success'));
        } catch (\Exception $e) {
            Log::error('Gmail Delete Error: ' . $e->getMessage());
            
            return back()->with('error', trans('google::app.gmail.delete-error'));
        }
    }

    /**
     * Parse comma-separated emails into array.
     */
    protected function parseEmails(string $emails): array
    {
        return array_map('trim', explode(',', $emails));
    }
}
