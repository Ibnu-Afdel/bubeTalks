<?php

namespace App\Jobs;

use App\Models\BubeMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateBubeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BubeMessage $message;


    /**
     * Create a new job instance.
     */
    public function __construct(BubeMessage $message)
    {
        $this->message = $message;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiKey = config('services.unrealspeech.key');
            $text = $this->message->response_text;

            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.unrealspeech.com/speech', [
                'Text' => $text,
                'VoiceId' => 'Matthew', // You can change to 'Scarlett', 'James', etc.
                'OutputFormat' => 'mp3',
                'Speed' => 1.0,
                'Bitrate' => '192k',
            ]);

            if (!$response->successful()) {
                throw new \Exception('UnrealSpeech Error: ' . $response->body());
            }

            $audio = $response->body();
            $filename = 'bube-audio/' . $this->message->id . '.mp3';

            Storage::disk('public')->put($filename, $audio);

            $this->message->update([
                'audio_url' => Storage::url($filename),
            ]);
        } catch (\Exception $e) {
            $this->message->update([
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
