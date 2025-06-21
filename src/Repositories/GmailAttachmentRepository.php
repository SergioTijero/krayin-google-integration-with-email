<?php

namespace Webkul\Google\Repositories;

use Webkul\Core\Eloquent\Repository;

class GmailAttachmentRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Google\Contracts\GmailAttachment';
    }

    /**
     * Get attachments for a message
     */
    public function getMessageAttachments(int $messageId)
    {
        return $this->where('message_id', $messageId)
                   ->orderBy('filename')
                   ->get();
    }

    /**
     * Download attachment data from Gmail API
     */
    public function downloadAttachment(int $attachmentId, $account)
    {
        $attachment = $this->find($attachmentId);
        
        if (!$attachment || !$attachment->gmailMessage) {
            throw new \Exception('Attachment not found');
        }

        // If we already have the data cached, return it
        if ($attachment->data) {
            return base64_decode($attachment->data);
        }

        // Otherwise, fetch from Gmail API
        $google = app(\Webkul\Google\Services\Google::class)->connectUsing($account->token);
        
        $attachmentData = $google->gmail()->users_messages_attachments->get(
            'me',
            $attachment->gmailMessage->google_message_id,
            $attachment->google_attachment_id
        );

        $data = base64url_decode($attachmentData->getData());
        
        // Cache the data (optional - be careful with large files)
        if (strlen($data) < 5 * 1024 * 1024) { // Only cache files smaller than 5MB
            $attachment->update(['data' => base64_encode($data)]);
        }

        return $data;
    }
}
