<?php
namespace App\Service;

use App\LineBot\StockFlexMessage;
use App\LineBot\ManualMessage;
use App\LineBot\QuickReplyMessage;
use Illuminate\Support\Facades\Log;
use LINE\Clients\MessagingApi\Model\BroadcastRequest;
use LINE\Clients\MessagingApi\Model\Message;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Laravel\Facades\LINEMessagingApi;

class LineMessageService
{
    protected FinmindService $finmindService;

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
                $this->replyMessage($replyToken, StockFlexMessage::get($stockInfo));
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
                $currentPrice = $this->finmindService->getCurrentStockPrice($data['stock_id']);
                if ($currentPrice) {
                    $price = $currentPrice[0];
                    $text = "{$stockInfo['stock_name']} {$price['date']} 股價: {$price['sell_price']}";
                } else {
                    $text = '查無即時股價資訊';
                }
                $this->replyTextMessage($replyToken, $text);
                break;
            case 'trading_money':
                $stockInfo = $this->finmindService->searchStock($data['stock_id']);
                $text = "{$stockInfo['stock_name']} {$stockInfo['price']['date']} 交易總額: {$stockInfo['price']['Trading_money']}";
                $this->replyTextMessage($replyToken, $text);
                break;
            case 'trading_volume':
                $stockInfo = $this->finmindService->searchStock($data['stock_id']);
                $text = "{$stockInfo['stock_name']} {$stockInfo['price']['date']} 交易量: {$stockInfo['price']['Trading_Volume']}";
                $this->replyTextMessage($replyToken, $text);
                break;
            case 'other_date':
                $params = $postbackContent->getParams();
                $stockInfo = $this->finmindService->searchStock($data['stock_id'], $params['date']);
                if ($stockInfo) {
                    $this->replyMessage($replyToken, StockFlexMessage::get($stockInfo));
                } else {
                    $this->replyTextMessage($replyToken, '查無此日期資訊');
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

    private function replyTextMessage($replyToken, $text): void
    {
        $message = new TextMessage([
            'text' => $text,
            'type' => MessageType::TEXT,
        ]);
        $this->replyMessage($replyToken, $message);
    }

    public function manualPushMessage(string $title, string $content): void
    {
        $this->pushMessage(ManualMessage::get($title, $content));
    }

    /**
     * 群發訊息
     * @param $message
     * @return void
     */
    private function pushMessage($message): void
    {
        $request = new BroadcastRequest([
            'messages' => [$message],
        ]);
        try {
            LINEMessagingApi::broadcast($request);
        } catch (\Exception $e) {
            Log::error('broadcastRequest error', [$e->getMessage()]);
        }
    }

    /**
     * 回覆訊息
     * @param string $replyToken
     * @param Message $message
     * @return void
     */
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
