<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaPostRequest;
use App\Models\MediaPost;
use App\Services\SlackFileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class MediaPostController extends Controller
{
    public function index(): View
    {
        return view('media-posts.index', [
            'mediaPosts' => MediaPost::with('user')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('media-posts.create');
    }

    public function store(StoreMediaPostRequest $request, SlackFileUploader $uploader): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('media');

        try {
            $slackUpload = $uploader->upload(
                $file,
                $validated['title'],
                "Nuevo archivo: {$validated['title']}"
            );
        } catch (Throwable $exception) {
            Log::error('Slack upload failed', [
                'message' => $exception->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['media' => 'No se pudo subir el archivo a Slack. Revisa la configuracion del bot y el canal.']);
        }

        MediaPost::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'published_on' => $validated['published_on'],
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'slack_file_id' => $slackUpload['file_id'],
            'slack_channel_id' => $slackUpload['channel_id'],
            'slack_permalink' => $slackUpload['permalink'],
            'slack_private_url' => $slackUpload['private_url'],
            'slack_response' => $slackUpload['response'],
        ]);

        return redirect()
            ->route('media-posts.index')
            ->with('status', 'Archivo subido a Slack correctamente.');
    }
}
