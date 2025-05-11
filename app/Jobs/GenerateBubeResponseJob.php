<?php

namespace App\Jobs;

use App\Models\BubeMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateBubeResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BubeMessage $message;

    public function __construct(BubeMessage $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            $apiKey = config('services.gemini.key');

            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Answer this wisely, as if you're a mysterious oracle: {$this->message->question}"]
                        ]
                    ]
                ]
            ]);

            if (!$response->successful()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $data = $response->json();
            $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$aiResponse) {
                throw new \Exception('Empty AI response');
            }

            // Save response to database
            $this->message->update([
                'response_text' => $aiResponse,
                'status' => 'completed',
            ]);

            // 👇 Next: In Step 3 we'll dispatch a job to generate the voice
            // GenerateBubeAudioJob::dispatch($this->message);

        } catch (\Exception $e) {
            $this->message->update([
                'status' => 'completed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
