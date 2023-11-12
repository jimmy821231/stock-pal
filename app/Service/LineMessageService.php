<?php

namespace App\Service;
use App\Service\Line\QuickReplyMessage;
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
            $stockInfo = $this->finmindService->searchStock($text);
            Log::info('stock', [$stockInfo]);
            if ($stockInfo) {
                $this->replyMessage($replyToken, FlexMessage::get($stockInfo));
            } else {
                $message = new TextMessage([
                    'text' => '查無此股票代碼, 請重新輸入',
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
            }
        }
    }

    public function handelPostbackAction($event): void
    {
        $data = [];
        $replyToken = $event->getReplyToken();
        $postbackContent = $event->getPostback();
        parse_str($postbackContent->getData(), $data);
        Log::info('PostBackAction', [$data]);

        switch ($data['action']) {
            case 'more':
                $this->replyMessage($replyToken, QuickReplyMessage::get($data['stock_id']));
                break;
            case 'tick_snapshot':
                $stockInfo = $this->finmindService->searchStock($data['stock_id']);
                $message = new TextMessage([
                    'text' => $stockInfo['price']['close'],
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
                break;
            case 'trading_money':
                $stockInfo = $this->finmindService->searchStock($data['stock_id']);
                $message = new TextMessage([
                    'text' => $stockInfo['price']['Trading_money'],
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
                break;
            case 'trading_volume':
                $stockInfo = $this->finmindService->searchStock($data['stock_id']);
                Log::info('stockInfo', [$stockInfo]);
                $message = new TextMessage([
                    'text' => $stockInfo['price']['Trading_Volume'],
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
                break;
            case 'other_date':
                $params = $postbackContent->getParams();
                $stockInfo = $this->finmindService->searchStock($data['stock_id'], $params['date']);
                if ($stockInfo) {
                    $this->replyMessage($replyToken, FlexMessage::get($stockInfo));
                } else {
                    $message = new TextMessage([
                        'text' => '當日查無交易資料',
                        'type' => MessageType::TEXT,
                    ]);
                    $this->replyMessage($replyToken, $message);
                }
                break;
            default:
                $message = new TextMessage([
                    'text' => '請選擇',
                    'type' => MessageType::TEXT,
                ]);
                $this->replyMessage($replyToken, $message);
                break;
        }
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
