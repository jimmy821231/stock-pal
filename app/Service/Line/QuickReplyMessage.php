<?php

namespace App\Service\Line;

use LINE\Clients\MessagingApi\Model\DatetimePickerAction;
use LINE\Clients\MessagingApi\Model\PostbackAction;
use LINE\Clients\MessagingApi\Model\QuickReply;
use LINE\Clients\MessagingApi\Model\QuickReplyItem;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\ActionType;
use LINE\Constants\MessageType;

class QuickReplyMessage
{
    public static function get($stockId): TextMessage
    {
        $quickReply = new QuickReply([
            'items' => [
                new QuickReplyItem([
                    'type' => 'action',
                    'action' => new PostbackAction([
                        'type' => ActionType::POSTBACK,
                        'label' => '即時股價',
                        'text' => '即時股價',
                        'data' => 'action=tick_snapshot&stock_id='.$stockId,
                    ]),
                ]),
                new QuickReplyItem([
                    'type' => 'action',
                    'action' => new PostbackAction([
                        'type' => ActionType::POSTBACK,
                        'label' => '交易總額',
                        'text' => '交易總額',
                        'data' => 'action=trading_money&stock_id='.$stockId,
                    ]),
                ]),
                new QuickReplyItem([
                    'type' => 'action',
                    'action' => new PostbackAction([
                        'type' => ActionType::POSTBACK,
                        'label' => '交易量',
                        'text' => '交易量',
                        'data' => 'action=trading_volume&stock_id='.$stockId,
                    ]),
                ]),
                new QuickReplyItem([
                    'type' => 'action',
                    'action' => new DatetimePickerAction([
                        'type' => ActionType::DATETIME_PICKER,
                        'label' => '其他日期交易資訊',
                        'data' => 'action=other_date&stock_id='.$stockId,
                        'mode' => 'date ',
                        'initial' => now()->format('Y-m-d'),
                        'max' => now()->format('Y-m-d'),
                        'min' => now()->subDays(90)->format('Y-m-d'),
                    ]),
                ]),
            ]
        ]);
        return new TextMessage([
            'text' => '還想知道什麼資訊呢?',
            'type' => MessageType::TEXT,
            'quickReply' => $quickReply,
        ]);
    }
}
