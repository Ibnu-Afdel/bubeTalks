<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bube:check-integrations {--with-network}', function () {
    $this->info('🔍 Checking required integrations...');

    $geminiKey = config('services.gemini.key');
    $elevenKey = config('services.elevenlabs.key');
    $voiceId = config('services.elevenlabs.voice_id');
    $modelId = config('services.elevenlabs.model_id', 'eleven_turbo_v2');

    if ($geminiKey) {
        $this->info('✅ GEMINI_API_KEY is set.');
    } else {
        $this->error('❌ GEMINI_API_KEY is missing. Add it to your .env file.');
    }

    if ($elevenKey) {
        $this->info('✅ ELEVENLABS_API_KEY is set.');
    } else {
        $this->error('❌ ELEVENLABS_API_KEY is missing. Add it to your .env file.');
    }

    $this->line("ElevenLabs voice: " . ($voiceId ?: 'not configured'));
    $this->line("ElevenLabs model: {$modelId}");

    $elevenLabsHost = 'api.elevenlabs.io';
    $resolvedIp = gethostbyname($elevenLabsHost);

    if (! $resolvedIp || $resolvedIp === $elevenLabsHost) {
        $this->error("❌ Unable to resolve {$elevenLabsHost}. Check your DNS/network settings.");
    } else {
        $this->info("✅ {$elevenLabsHost} resolves to {$resolvedIp}.");
    }

    if (! $this->option('with-network')) {
        $this->comment('Tip: re-run with --with-network to perform live API checks.');
        return;
    }

    if ($geminiKey) {
        $this->comment('→ Pinging Gemini...');
        try {
            Http::timeout(10)
                ->acceptJson()
                ->get('https://generativelanguage.googleapis.com/v1beta/models', [
                    'key' => $geminiKey,
                    'pageSize' => 1,
                ])->throw();
            $this->info('   Gemini API reachable.');
        } catch (\Throwable $e) {
            $this->error('   Gemini API error: ' . $e->getMessage());
        }
    }

    if ($elevenKey) {
        $this->comment('→ Pinging ElevenLabs...');
        try {
            Http::timeout(10)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                    'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
                ])
                ->withHeaders([
                    'xi-api-key' => $elevenKey,
                ])
                ->get('https://api.elevenlabs.io/v1/voices')
                ->throw();
            $this->info('   ElevenLabs API reachable.');
        } catch (\Throwable $e) {
            $this->error('   ElevenLabs API error: ' . $e->getMessage());
        }
    }
})->purpose('Check API keys, DNS, and connectivity for Gemini and ElevenLabs');
