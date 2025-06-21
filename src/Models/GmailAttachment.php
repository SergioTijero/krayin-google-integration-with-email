<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Google\Contracts\GmailAttachment as GmailAttachmentContract;

class GmailAttachment extends Model implements GmailAttachmentContract
{
    protected $table = 'google_gmail_attachments';

    protected $fillable = [
        'message_id',
        'google_attachment_id',
        'filename',
        'mime_type',
        'size',
        'data',
        'is_inline',
    ];

    protected $casts = [
        'is_inline' => 'boolean',
        'size' => 'integer',
    ];

    /**
     * Get the Gmail message this attachment belongs to
     */
    public function gmailMessage(): BelongsTo
    {
        return $this->belongsTo(GmailMessageProxy::modelClass(), 'message_id');
    }

    /**
     * Get the file size in human readable format
     */
    public function getHumanSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
