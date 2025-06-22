<?php

namespace Webkul\Google\Mail\Transport;

use Illuminate\Mail\Transport\Transport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;
use Webkul\Google\Services\Google;

class GmailTransport extends Transport
{
    /**
     * The Google service instance.
     */
    protected Google $google;

    /**
     * The Gmail account to use for sending.
     */
    protected $account;

    /**
     * Create a new Gmail transport instance.
     */
    public function __construct(Google $google, $account = null)
    {
        parent::__construct();
        
        $this->google = $google;
        $this->account = $account;
    }

    /**
     * Send a message via Gmail API.
     */
    public function doSend(SentMessage $message): void
    {
        $this->beforeSendPerformed($message);

        try {
            // Convert Symfony message to Gmail format
            $envelope = $message->getEnvelope();
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            // Prepare email data for Gmail API
            $emailData = [
                'to' => $this->getRecipientsAsString($envelope->getRecipients()),
                'subject' => $email->getSubject(),
                'body' => $email->getHtmlBody() ?: $email->getTextBody(),
                'from' => $envelope->getSender()->toString(),
            ];

            // Add CC and BCC if present
            if (!empty($email->getCc())) {
                $emailData['cc'] = $this->getRecipientsAsString($email->getCc());
            }

            if (!empty($email->getBcc())) {
                $emailData['bcc'] = $this->getRecipientsAsString($email->getBcc());
            }

            // Connect with the account token and send
            if ($this->account) {
                $this->google->connectUsing($this->account->token);
            }

            $this->google->sendEmail($emailData);

            $this->sendPerformed($message);

        } catch (\Exception $e) {
            throw new \Exception("Failed to send email via Gmail API: " . $e->getMessage());
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'gmail';
    }

    /**
     * Convert recipients array to comma-separated string.
     */
    protected function getRecipientsAsString(array $recipients): string
    {
        return implode(', ', array_map(function ($recipient) {
            return $recipient->toString();
        }, $recipients));
    }
}
