<?php

namespace Webkul\Google\Services;

use Illuminate\Support\Facades\Log;
use Webkul\Google\Models\Account;
use Webkul\Google\Repositories\GmailMessageRepository;
use Webkul\Google\Repositories\GmailAttachmentRepository;

class GmailService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected Google $google,
        protected GmailMessageRepository $gmailMessageRepository,
        protected GmailAttachmentRepository $gmailAttachmentRepository
    ) {}

    /**
     * Get Gmail messages for an account.
     */
    public function getMessages(Account $account, array $options = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->gmailMessageRepository->where('account_id', $account->id);

        // Apply folder filter
        switch ($options['folder'] ?? 'inbox') {
            case 'sent':
                $query->sent();
                break;
            case 'drafts':
                $query->drafts();
                break;
            case 'trash':
                $query->where('is_trash', true);
                break;
            case 'spam':
                $query->where('is_spam', true);
                break;
            default:
                $query->inbox();
                break;
        }

        return $query->orderBy('internal_date', 'desc')
                    ->paginate($options['maxResults'] ?? 50);
    }

    /**
     * Get a specific Gmail message.
     */
    public function getMessage(Account $account, string $messageId)
    {
        // First try to get from our database
        $message = $this->gmailMessageRepository->where('account_id', $account->id)
            ->where('google_message_id', $messageId)
            ->first();

        if (!$message) {
            // Sync the message from Gmail API
            $message = $this->syncMessage($account, $messageId);
        }

        return $message;
    }

    /**
     * Send an email via Gmail API.
     */
    public function sendEmail(Account $account, array $emailData): \Google_Service_Gmail_Message
    {
        $google = $this->google->connectUsing($account->token);
        
        return $google->sendEmail($emailData);
    }

    /**
     * Sync messages from Gmail API.
     */
    public function syncMessages(Account $account, array $options = []): int
    {
        $google = $this->google->connectUsing($account->token);
        
        $maxResults = $options['maxResults'] ?? 100;
        $query = $options['query'] ?? '';
        
        $messagesResponse = $google->getMessages([
            'maxResults' => $maxResults,
            'q' => $query
        ]);

        $syncedCount = 0;

        if ($messagesResponse->getMessages()) {
            foreach ($messagesResponse->getMessages() as $messageRef) {
                try {
                    $this->syncMessage($account, $messageRef->getId());
                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync message {$messageRef->getId()}: " . $e->getMessage());
                }
            }
        }

        return $syncedCount;
    }

    /**
     * Sync a single message from Gmail API.
     */
    public function syncMessage(Account $account, string $messageId)
    {
        $google = $this->google->connectUsing($account->token);
        
        $gmailMessage = $google->getMessage($messageId);

        // Parse the message data
        $messageData = $this->parseGmailMessage($account, $gmailMessage);

        // Update or create the message in our database
        $message = $this->gmailMessageRepository->updateOrCreate(
            [
                'account_id' => $account->id,
                'google_message_id' => $messageId
            ],
            $messageData
        );

        // Sync attachments if any
        if ($gmailMessage->getPayload() && $gmailMessage->getPayload()->getParts()) {
            $this->syncAttachments($account, $message, $gmailMessage);
        }

        return $message;
    }

    /**
     * Parse Gmail message data.
     */
    protected function parseGmailMessage(Account $account, \Google_Service_Gmail_Message $gmailMessage): array
    {
        $payload = $gmailMessage->getPayload();
        $headers = $payload ? $payload->getHeaders() : [];

        // Extract headers
        $from = $this->getHeaderValue($headers, 'From');
        $to = $this->parseEmailAddresses($this->getHeaderValue($headers, 'To'));
        $cc = $this->parseEmailAddresses($this->getHeaderValue($headers, 'Cc'));
        $bcc = $this->parseEmailAddresses($this->getHeaderValue($headers, 'Bcc'));
        $subject = $this->getHeaderValue($headers, 'Subject');

        // Extract body
        $bodyData = $this->extractMessageBody($payload);

        // Determine message status from labels
        $labelIds = $gmailMessage->getLabelIds() ?: [];
        
        return [
            'google_message_id' => $gmailMessage->getId(),
            'thread_id' => $gmailMessage->getThreadId(),
            'label_ids' => $labelIds,
            'snippet' => $gmailMessage->getSnippet(),
            'history_id' => $gmailMessage->getHistoryId(),
            'internal_date' => $this->convertTimestamp($gmailMessage->getInternalDate()),
            'size_estimate' => $gmailMessage->getSizeEstimate(),
            'payload' => $payload ? $payload->toArray() : null,
            'from' => $from,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $subject,
            'body_html' => $bodyData['html'],
            'body_text' => $bodyData['text'],
            'is_read' => !in_array('UNREAD', $labelIds),
            'is_starred' => in_array('STARRED', $labelIds),
            'is_important' => in_array('IMPORTANT', $labelIds),
            'is_draft' => in_array('DRAFT', $labelIds),
            'is_sent' => in_array('SENT', $labelIds),
            'is_trash' => in_array('TRASH', $labelIds),
            'is_spam' => in_array('SPAM', $labelIds),
            'raw_data' => $gmailMessage->toArray(),
        ];
    }

    /**
     * Extract message body from payload.
     */
    protected function extractMessageBody($payload): array
    {
        $body = ['html' => '', 'text' => ''];

        if (!$payload) {
            return $body;
        }

        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                $this->extractBodyFromPart($part, $body);
            }
        } else {
            $this->extractBodyFromPart($payload, $body);
        }

        return $body;
    }

    /**
     * Extract body content from a message part.
     */
    protected function extractBodyFromPart($part, &$body): void
    {
        $mimeType = $part->getMimeType();
        
        if ($mimeType === 'text/plain' && $part->getBody() && $part->getBody()->getData()) {
            $body['text'] = base64url_decode($part->getBody()->getData());
        } elseif ($mimeType === 'text/html' && $part->getBody() && $part->getBody()->getData()) {
            $body['html'] = base64url_decode($part->getBody()->getData());
        } elseif ($part->getParts()) {
            foreach ($part->getParts() as $subPart) {
                $this->extractBodyFromPart($subPart, $body);
            }
        }
    }

    /**
     * Sync attachments for a message.
     */
    protected function syncAttachments(Account $account, $message, \Google_Service_Gmail_Message $gmailMessage): void
    {
        $payload = $gmailMessage->getPayload();
        
        if (!$payload || !$payload->getParts()) {
            return;
        }

        foreach ($payload->getParts() as $part) {
            $this->processAttachmentPart($account, $message, $part);
        }
    }

    /**
     * Process a message part for attachments.
     */
    protected function processAttachmentPart(Account $account, $message, $part): void
    {
        if ($part->getFilename() && $part->getBody() && $part->getBody()->getAttachmentId()) {
            $attachmentData = [
                'message_id' => $message->id,
                'google_attachment_id' => $part->getBody()->getAttachmentId(),
                'filename' => $part->getFilename(),
                'mime_type' => $part->getMimeType(),
                'size' => $part->getBody()->getSize() ?: 0,
                'is_inline' => strpos($part->getMimeType(), 'image/') === 0,
            ];

            $this->gmailAttachmentRepository->updateOrCreate(
                [
                    'message_id' => $message->id,
                    'google_attachment_id' => $part->getBody()->getAttachmentId()
                ],
                $attachmentData
            );
        }

        // Process nested parts
        if ($part->getParts()) {
            foreach ($part->getParts() as $subPart) {
                $this->processAttachmentPart($account, $message, $subPart);
            }
        }
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(Account $account, string $messageId): void
    {
        $google = $this->google->connectUsing($account->token);
        
        $modifyRequest = new \Google_Service_Gmail_ModifyMessageRequest();
        $modifyRequest->setRemoveLabelIds(['UNREAD']);
        
        $google->gmail()->users_messages->modify('me', $messageId, $modifyRequest);
    }

    /**
     * Delete/trash a message.
     */
    public function deleteMessage(Account $account, string $messageId): void
    {
        $google = $this->google->connectUsing($account->token);
        
        $google->gmail()->users_messages->trash('me', $messageId);

        // Update our local copy
        $this->gmailMessageRepository->where('account_id', $account->id)
            ->where('google_message_id', $messageId)
            ->update(['is_trash' => true]);
    }

    /**
     * Get header value by name.
     */
    protected function getHeaderValue(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if (strtolower($header->getName()) === strtolower($name)) {
                return $header->getValue();
            }
        }

        return null;
    }

    /**
     * Parse email addresses from header string.
     */
    protected function parseEmailAddresses(?string $headerValue): array
    {
        if (!$headerValue) {
            return [];
        }

        // Simple email parsing - could be enhanced for complex cases
        $emails = array_map('trim', explode(',', $headerValue));
        
        return array_map(function ($email) {
            // Extract email from "Name <email@domain.com>" format
            if (preg_match('/<(.+?)>/', $email, $matches)) {
                return $matches[1];
            }
            return $email;
        }, $emails);
    }

    /**
     * Convert Gmail timestamp to Carbon instance.
     */
    protected function convertTimestamp($timestamp)
    {
        return $timestamp ? \Carbon\Carbon::createFromTimestampMs($timestamp) : null;
    }
}

/**
 * Helper function for base64url decoding
 */
if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
