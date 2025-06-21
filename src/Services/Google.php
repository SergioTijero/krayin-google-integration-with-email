<?php

namespace Webkul\Google\Services;

use Webkul\Google\Models\Account;
use Webkul\Google\Models\Calendar;

class Google
{
    /**
     * Google Client object
     *
     * @var \Google_Client
     */
    protected $client;

    /**
     * Gmail service instance
     *
     * @var \Google_Service_Gmail|null
     */
    protected $gmailService;

    /**
     * Google service constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $client = new \Google_Client;

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setScopes(config('services.google.scopes'));
        $client->setApprovalPrompt(config('services.google.approval_prompt'));
        $client->setAccessType(config('services.google.access_type'));
        $client->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));

        $this->client = $client;
    }

    /**
     * Dynamically call methods on the Google client.
     */
    public function __call($method, $args): mixed
    {
        if (! method_exists($this->client, $method)) {
            throw new \Exception("Call to undefined method '{$method}'");
        }

        return call_user_func_array([$this->client, $method], $args);
    }

    /**
     * Create a new Google service instance.
     */
    public function service($service): mixed
    {
        $className = "Google_Service_$service";

        return new $className($this->client);
    }

    /**
     * Connect to Google using the given token.
     */
    public function connectUsing(string|array $token): self
    {
        $this->client->setAccessToken($token);

        return $this;
    }

    /**
     * Create a new Google service instance.
     */
    public function revokeToken(string|array|null $token = null): bool
    {
        $token = $token ?? $this->client->getAccessToken();

        return $this->client->revokeToken($token);
    }

    /**
     * Connect to Google using the given synchronizable.
     */
    public function connectWithSynchronizable(mixed $synchronizable): self
    {
        $token = $this->getTokenFromSynchronizable($synchronizable);

        return $this->connectUsing($token);
    }

    /**
     * Get the token from the given synchronizable.
     */
    protected function getTokenFromSynchronizable(mixed $synchronizable): mixed
    {
        switch (true) {
            case $synchronizable instanceof Account:
                return $synchronizable->token;

            case $synchronizable instanceof Calendar:
                return $synchronizable->account->token;

            default:
                throw new \Exception('Invalid Synchronizable');
        }
    }

    /**
     * Get Gmail service instance
     */
    public function gmail(): \Google_Service_Gmail
    {
        if (!$this->gmailService) {
            $this->gmailService = new \Google_Service_Gmail($this->client);
        }

        return $this->gmailService;
    }

    /**
     * Send email via Gmail API
     */
    public function sendEmail(array $emailData): \Google_Service_Gmail_Message
    {
        $gmail = $this->gmail();
        
        $message = new \Google_Service_Gmail_Message();
        $message->setRaw($this->createRawMessage($emailData));

        return $gmail->users_messages->send('me', $message);
    }

    /**
     * Get Gmail messages with optional query
     */
    public function getMessages(array $options = []): \Google_Service_Gmail_ListMessagesResponse
    {
        $gmail = $this->gmail();
        
        $params = [
            'maxResults' => $options['maxResults'] ?? 100,
        ];

        if (isset($options['q'])) {
            $params['q'] = $options['q'];
        }

        if (isset($options['pageToken'])) {
            $params['pageToken'] = $options['pageToken'];
        }

        return $gmail->users_messages->listUsersMessages('me', $params);
    }

    /**
     * Get Gmail message by ID
     */
    public function getMessage(string $messageId): \Google_Service_Gmail_Message
    {
        $gmail = $this->gmail();
        
        return $gmail->users_messages->get('me', $messageId, ['format' => 'full']);
    }

    /**
     * Get Gmail labels
     */
    public function getLabels(): \Google_Service_Gmail_ListLabelsResponse
    {
        $gmail = $this->gmail();
        
        return $gmail->users_labels->listUsersLabels('me');
    }

    /**
     * Create raw message for Gmail API
     */
    protected function createRawMessage(array $emailData): string
    {
        $to = is_array($emailData['to']) ? implode(', ', $emailData['to']) : $emailData['to'];
        $subject = $emailData['subject'] ?? '';
        $body = $emailData['body'] ?? '';
        $from = $emailData['from'] ?? config('mail.from.address');
        
        $headers = [];
        $headers[] = "To: {$to}";
        $headers[] = "From: {$from}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=utf-8";
        $headers[] = "Content-Transfer-Encoding: base64";

        if (isset($emailData['cc']) && !empty($emailData['cc'])) {
            $cc = is_array($emailData['cc']) ? implode(', ', $emailData['cc']) : $emailData['cc'];
            $headers[] = "Cc: {$cc}";
        }

        if (isset($emailData['bcc']) && !empty($emailData['bcc'])) {
            $bcc = is_array($emailData['bcc']) ? implode(', ', $emailData['bcc']) : $emailData['bcc'];
            $headers[] = "Bcc: {$bcc}";
        }

        $rawMessage = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        
        return base64url_encode($rawMessage);
    }
}

/**
 * Helper function for base64url encoding required by Gmail API
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
