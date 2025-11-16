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
use Illuminate\Support\Str;

class GenerateBubeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BubeMessage $message;
    private const CHUNK_SIZE = 1000; // Characters per chunk
    private const TIMEOUT = 120; // 2 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(BubeMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Clean text by removing markdown formatting
     */
    private function cleanText(string $text): string
    {
        // Remove markdown headers
        $text = preg_replace('/^#+\s+/m', '', $text);
        
        // Remove bold/italic markers
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);
        
        // Remove code blocks
        $text = preg_replace('/```.*?```/s', '', $text);
        
        // Remove inline code
        $text = preg_replace('/`(.*?)`/', '$1', $text);
        
        // Remove horizontal rules
        $text = preg_replace('/^[-*_]{3,}$/m', '', $text);
        
        // Clean up multiple newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text);
    }

    /**
     * Split text into chunks
     */
    private function splitIntoChunks(string $text): array
    {
        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (strlen($currentChunk . $sentence) > self::CHUNK_SIZE) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }
                // If a single sentence is longer than chunk size, split it by commas
                if (strlen($sentence) > self::CHUNK_SIZE) {
                    $parts = preg_split('/(?<=[,;])\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($parts as $part) {
                        if (strlen($currentChunk . $part) > self::CHUNK_SIZE) {
                            if (!empty($currentChunk)) {
                                $chunks[] = trim($currentChunk);
                                $currentChunk = '';
                            }
                            $chunks[] = trim($part);
                        } else {
                            $currentChunk .= ' ' . $part;
                        }
                    }
                } else {
                    $chunks[] = trim($sentence);
                }
            } else {
                $currentChunk .= ' ' . $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Generate audio for a single chunk
     */
    private function generateAudioChunk(string $text, string $apiKey, string $voiceId, string $modelId): string
    {
        $response = Http::timeout(self::TIMEOUT)
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->withHeaders([
                'xi-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => $modelId,
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('ElevenLabs Error: ' . $response->body());
        }

        return $response->body();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiKey = config('services.elevenlabs.key');
            $voiceId = config('services.elevenlabs.voice_id');
            $modelId = config('services.elevenlabs.model_id', 'eleven_turbo_v2');
            $text = $this->cleanText($this->message->response_text);
            
            // Split text into chunks
            $chunks = $this->splitIntoChunks($text);
            $audioChunks = [];

            // Generate audio for each chunk
            foreach ($chunks as $chunk) {
                $audioChunks[] = $this->generateAudioChunk($chunk, $apiKey, $voiceId, $modelId);
            }

            // Combine audio chunks
            $combinedAudio = '';
            foreach ($audioChunks as $chunk) {
                $combinedAudio .= $chunk;
            }

            $filename = 'bube-audio/' . $this->message->id . '.mp3';
            Storage::disk('public')->put($filename, $combinedAudio);

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
