<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'published_on',
        'original_filename',
        'mime_type',
        'size',
        'slack_file_id',
        'slack_channel_id',
        'slack_permalink',
        'slack_private_url',
        'slack_response',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'slack_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function slackUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->slack_permalink) {
                return $this->slack_permalink;
            }

            if ($this->slack_private_url) {
                return $this->slack_private_url;
            }

            $file = $this->slack_response['files'][0]
                ?? $this->slack_response['file']
                ?? null;

            if (! is_array($file)) {
                return null;
            }

            return $file['permalink'] ?? $file['url_private'] ?? null;
        });
    }
}
