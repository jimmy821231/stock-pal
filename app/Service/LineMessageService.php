<?php

namespace App\Service;
use Illuminate\Support\Facades\Log;
use LINE\Clients\MessagingApi\Model\Message;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Laravel\Facades\LINEMessagingApi;
use App\Service\Line\FlexMessage;


class LineMessageService
{
    protected $client;
    protected $finmindService;
    public function __construct(FinmindService $finmindService)
    {
        $this->finmindService = $finmindService;
    }

    public function handelMessage($event): void
    {
        $message = $event->getMessage();
        $text = $message->getText();
        $replyToken = $event->getReplyToken();
        Log::info("Got text message from $replyToken: $text");
        if (is_numeric($text)) {
            $stockInfo = $this->finmindService->getStockInfo();
            $stockInfo = collect($stockInfo)->firstWhere('stock_id', $text);
            Log::info('stock', [$stockInfo]);
            if ($stockInfo) {
                $threeDaysAgo = now()->subDays(3)->format('Y-m-d');
                $stockPrice = $this->finmindService->getStockPrice($text, $threeDaysAgo);
                $stockPrice = collect($stockPrice)->last();
                $stockInfo['price'] = $stockPrice;
                Log::info('stock', [$stockInfo]);
                $this->replyMessage($replyToken, FlexMessage::get($stockInfo));
            } else {
                Log::info('No stock Info', [$stockInfo]);
                $message = new TextMessage([
                    'text' => '查無此股票代碼, 請重新輸入',
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
            }
        } elseif ($text === '股票列表') {
            $stockInfo = app(FinmindService::class)->getStockInfo();
            $text = '';
            foreach ($stockInfo as $stock) {
                $text .= "{$stock['stock_id']} {$stock['stock_name']}\n";
            }
        }
    }

    public function handelPostbackAction($event): void
    {
        $replyToken = $event->getReplyToken();
        $data = $event->getPostback();
        Log::info('PostBackAction', [$data]);
    }

    private function replyMessage(string $replyToken, Message $message): void
    {
        $request = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$message],
        ]);
        try {
            LINEMessagingApi::replyMessage($request);
        } catch (\Exception $e) {
            Log::error('replyMessage error', [$e->getMessage()]);
        }
    }
}
