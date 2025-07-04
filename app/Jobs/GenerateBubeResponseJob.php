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
            // dd($apiKey);


            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "You are my witty, super-smart best friend. Give me a short, funny, and confident answer to this : {$this->message->question}"]
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
