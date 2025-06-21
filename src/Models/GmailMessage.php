<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Google\Contracts\GmailMessage as GmailMessageContract;

class GmailMessage extends Model implements GmailMessageContract
{
    protected $table = 'google_gmail_messages';

    protected $fillable = [
        'account_id',
        'google_message_id',
        'thread_id',
        'label_ids',
        'snippet',
        'history_id',
        'internal_date',
        'size_estimate',
        'payload',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body_html',
        'body_text',
        'attachments',
        'is_read',
        'is_starred',
        'is_important',
        'is_draft',
        'is_sent',
        'is_trash',
        'is_spam',
        'raw_data',
    ];

    protected $casts = [
        'label_ids' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'payload' => 'array',
        'raw_data' => 'array',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'is_draft' => 'boolean',
        'is_sent' => 'boolean',
        'is_trash' => 'boolean',
        'is_spam' => 'boolean',
        'internal_date' => 'datetime',
    ];

    /**
     * Get the Google account that owns this message
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountProxy::modelClass(), 'account_id');
    }

    /**
     * Get the related Krayin email record (if linked)
     */
    public function krayinEmail(): BelongsTo
    {
        return $this->belongsTo(config('email.models.email', \Webkul\Email\Models\Email::class), 'krayin_email_id');
    }

    /**
     * Get message attachments
     */
    public function gmailAttachments(): HasMany
    {
        return $this->hasMany(GmailAttachmentProxy::modelClass(), 'message_id');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope for drafts
     */
    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope for inbox messages (not trash, not spam, not sent)
     */
    public function scopeInbox($query)
    {
        return $query->where('is_trash', false)
                    ->where('is_spam', false)
                    ->where('is_sent', false);
    }

    /**
     * Get the primary recipient email
     */
    public function getPrimaryToAttribute()
    {
        if (is_array($this->to) && count($this->to) > 0) {
            return $this->to[0];
        }

        return null;
    }

    /**
     * Get a shortened version of the body for display
     */
    public function getPreviewAttribute()
    {
        $text = $this->body_text ?: strip_tags($this->body_html);
        return \Str::limit($text, 150);
    }
}
