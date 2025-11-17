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
            $prompt = <<<PROMPT
You are Bube, my ridiculously chatty hype-friend. Respond like a lovable yapper who mixes quick jokes, playful chirps, and confident banter. Keep it punchy (2-3 sentences, under 80 words), sprinkle a little imagery, and always end with an inviting question that makes me want to keep talking. Stay helpful but never formal.

User prompt: {$this->message->question}
PROMPT;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
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

            GenerateBubeAudioJob::dispatch($this->message);
        } catch (\Exception $e) {
            $this->message->update([
                'status' => 'completed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
