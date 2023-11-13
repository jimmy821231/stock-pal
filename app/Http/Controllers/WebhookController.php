<?php

namespace App\Http\Controllers;

use App\Service\LineMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\PostbackEvent;

class WebhookController extends Controller
{
    /**
     * @var LineMessageService
     */
    protected LineMessageService $lineMessageService;
    public function __construct(LineMessageService $lineMessageService)
    {
        $this->lineMessageService = $lineMessageService;
    }

    public function getMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::info('input', $request->all());
        try {
            $signature = $request->header('X-Line-Signature');
            $secret = config('line_bot.channel_secret');

            $parsedEvents = EventRequestParser::parseEventRequest(
                $request->getContent(), $secret, $signature
            );
        } catch (\Exception $e) {
            Log::error('parsedEvents error', [$e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }

        foreach ($parsedEvents->getEvents() as $event) {
            if ($event instanceof MessageEvent) {
                $this->lineMessageService->handleMessage($event);
            } elseif ($event instanceof PostbackEvent) {
                $this->lineMessageService->handlePostbackAction($event);
            }
        }
        return response()->json(['success' => true]);
    }
}
