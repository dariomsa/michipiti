<?php

namespace App\Services\Slack;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackNotificationService
{
    protected bool $enabled;

    protected ?string $token;

    protected bool $testOnly;

    protected ?string $testEmail;

    public function __construct()
    {
        $this->enabled = (bool) config('services.slack.enabled', true);
        $this->token = config('services.slack.bot_token');
        $this->testOnly = (bool) env('SLACK_TEST_ONLY', false);
        $this->testEmail = env('SLACK_TEST_EMAIL');
    }

    protected function http()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->asJson();
    }

    public function userIdByEmail(?string $email): ?string
    {
        if (! $this->enabled || ! $this->token || ! $email) {
            return null;
        }

        $response = $this->http()
            ->get('https://slack.com/api/users.lookupByEmail', [
                'email' => $email,
            ])
            ->json();

        if (empty($response['ok'])) {
            Log::warning('[SLACK] users.lookupByEmail error', [
                'email' => $email,
                'response' => $response,
            ]);

            return null;
        }

        return $response['user']['id'] ?? null;
    }

    public function sendDM(User $user, string $text): bool
    {
        if (! $this->enabled || ! $this->token) {
            return false;
        }

        $email = $user->email_slack ?: $user->email;

        if ($this->testOnly) {
            if (! $this->testEmail) {
                return false;
            }

            if (mb_strtolower(trim((string) $email)) !== mb_strtolower(trim((string) $this->testEmail))) {
                return true;
            }
        }

        if (! $email) {
            Log::warning('[SLACK] usuario sin email', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        $slackUserId = $user->slack_user_id ?: $this->userIdByEmail($email);

        if (! $slackUserId) {
            Log::warning('[SLACK] usuario no encontrado en Slack', [
                'user_id' => $user->id,
                'email' => $email,
            ]);

            return false;
        }

        if (! $user->slack_user_id) {
            $user->slack_user_id = $slackUserId;
            $user->save();
        }

        $response = $this->http()
            ->post('https://slack.com/api/chat.postMessage', [
                'channel' => $slackUserId,
                'text' => $text,
            ])
            ->json();

        if (empty($response['ok'])) {
            Log::warning('[SLACK] chat.postMessage error', [
                'channel' => $slackUserId,
                'user_id' => $user->id,
                'email' => $email,
                'response' => $response,
            ]);

            return false;
        }

        return true;
    }
}
