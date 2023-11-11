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
        $signature = $request->header('X-Line-Signature');
        try {
            $secret = config('line_bot.channel_secret');
            Log::info('secret', [$secret]);
            Log::info('signature', [$signature]);

            $parsedEvents = EventRequestParser::parseEventRequest(
                $request->getContent(), $secret, $signature
            );
        } catch (\Exception $e) {
            Log::info('parsedEvents error', [$e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }

        Log::info('parsedEvents success');
        foreach ($parsedEvents->getEvents() as $event) {
            if ($event instanceof MessageEvent) {
                Log::info('get Message event');
                $this->lineMessageService->handelMessage($event);
//            } elseif ($event instanceof PostbackEvent) {
//                Log::info('get PostbackAction event');
//                $this->lineMessageService->handelPostbackAction($event);
            }
        }
        Log::info('input', $request->all());
        return response()->json(['success' => true]);
    }
}
