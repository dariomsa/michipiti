<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SlackFileUploader
{
    public function upload(UploadedFile $file, string $title, string $initialComment = ''): array
    {
        $token = config('services.slack.bot_token');
        $channelId = config('services.slack.channel_id');

        if (! $token || ! $channelId) {
            throw new RuntimeException('Slack is not configured. Set SLACK_BOT_TOKEN and SLACK_CHANNEL_ID.');
        }

        $uploadTicket = $this->slack($token)
            ->asForm()
            ->post('https://slack.com/api/files.getUploadURLExternal', [
                'filename' => $file->getClientOriginalName(),
                'length' => $file->getSize(),
            ])
            ->throw()
            ->json();

        $this->ensureSlackOk($uploadTicket, 'files.getUploadURLExternal');

        Http::withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
            ->post($uploadTicket['upload_url'])
            ->throw();

        $completePayload = [
            'channel_id' => $channelId,
            'files' => [
                [
                    'id' => $uploadTicket['file_id'],
                    'title' => $title,
                ],
            ],
        ];

        if ($initialComment !== '') {
            $completePayload['initial_comment'] = $initialComment;
        }

        $completedUpload = $this->slack($token)
            ->post('https://slack.com/api/files.completeUploadExternal', $completePayload)
            ->throw()
            ->json();

        $this->ensureSlackOk($completedUpload, 'files.completeUploadExternal');

        return [
            'file_id' => $uploadTicket['file_id'],
            'channel_id' => $channelId,
            'permalink' => $this->fileValue($completedUpload, 'permalink'),
            'private_url' => $this->fileValue($completedUpload, 'url_private'),
            'response' => $completedUpload,
        ];
    }

    private function slack(string $token): PendingRequest
    {
        return Http::withToken($token)->acceptJson();
    }

    private function ensureSlackOk(array $response, string $method): void
    {
        if (($response['ok'] ?? false) !== true) {
            $error = $response['error'] ?? 'unknown_error';

            throw new RuntimeException("Slack {$method} failed: {$error}");
        }
    }

    private function fileValue(array $response, string $key): ?string
    {
        $file = $response['files'][0] ?? $response['file'] ?? null;

        if (! is_array($file)) {
            return null;
        }

        return $file[$key] ?? null;
    }
}
