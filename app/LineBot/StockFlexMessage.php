<?php
namespace App\LineBot;

use LINE\Clients\MessagingApi\Model\FlexBox;
use LINE\Clients\MessagingApi\Model\FlexBubble;
use LINE\Clients\MessagingApi\Model\FlexButton;
use LINE\Clients\MessagingApi\Model\FlexMessage;
use LINE\Clients\MessagingApi\Model\FlexText;
use LINE\Clients\MessagingApi\Model\PostbackAction;
use LINE\Constants\ActionType;
use LINE\Constants\Flex\BubbleContainerSize;
use LINE\Constants\Flex\ComponentButtonHeight;
use LINE\Constants\Flex\ComponentButtonStyle;
use LINE\Constants\Flex\ComponentFontSize;
use LINE\Constants\Flex\ComponentFontWeight;
use LINE\Constants\Flex\ComponentLayout;
use LINE\Constants\Flex\ComponentMargin;
use LINE\Constants\Flex\ComponentSpacing;
use LINE\Constants\Flex\ComponentType;
use LINE\Constants\Flex\ContainerType;
use LINE\Constants\MessageType;

class StockFlexMessage
{
    /**
     * @param $stockInfo
     * @return FlexMessage
     */
    public static function get($stockInfo): FlexMessage
    {
        return new FlexMessage([
            'type' => MessageType::FLEX,
            'altText' => $stockInfo['stock_name'].' 股價資訊',
            'contents' => new FlexBubble([
                'type' => ContainerType::BUBBLE,
                'body' => self::createBodyBlock($stockInfo),
                'footer' => self::createFooterBlock($stockInfo['stock_id']),
                'size' => BubbleContainerSize::GIGA,
            ])
        ]);
    }

    private static function createBodyBlock($stockInfo): FlexBox
    {
        return new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::VERTICAL,
            'backgroundColor' => '#fafafa',
            'paddingAll' => '8%',
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $stockInfo['stock_name'],
                    'weight' => ComponentFontWeight::BOLD,
                    'size' => ComponentFontSize::XL,
                ]),
                self::createBodyInfoBlock($stockInfo['price']),
            ],
        ]);
    }

    private static function createBodyInfoBlock($stockPrice): FlexBox
    {
        $tradeDate = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '日期',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $stockPrice['date'],
                    'wrap' => true,
                    'color' => '#666666',
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);
        $open = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '開盤價',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => (string)$stockPrice['open'],
                    'wrap' => true,
                    'color' => '#666666',
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);
        $close = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '收盤價',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => (string)$stockPrice['close'],
                    'wrap' => true,
                    'color' => '#666666',
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);
        $max = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '最高價',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => (string)$stockPrice['max'],
                    'wrap' => true,
                    'color' => '#666666',
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);
        $min = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '最低價',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => (string)$stockPrice['min'],
                    'wrap' => true,
                    'color' => '#666666',
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);
        if ($stockPrice['spread'] > 0) {
            $spreadColor = '#ff333a';
        } elseif ($stockPrice['spread'] < 0) {
            $spreadColor = '#00ab5e';
        } else {
            $spreadColor = '#666666';
        }
        $spread = new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::BASELINE,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '漲跌幅',
                    'color' => '#aaaaaa',
                    'size' => ComponentFontSize::SM,
                    'flex' => 1,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $stockPrice['spread'] > 0 ? '+' . $stockPrice['spread'] : (string)$stockPrice['spread'],
                    'wrap' => true,
                    'color' => $spreadColor,
                    'size' => ComponentFontSize::SM,
                    'flex' => 5,
                ]),
            ],
        ]);

        return new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::VERTICAL,
            'margin' => ComponentMargin::LG,
            'spacing' => ComponentSpacing::SM,
            'contents' => [
                $tradeDate,
                $open,
                $close,
                $max,
                $min,
                $spread,
            ],
        ]);
    }

    private static function createFooterBlock($stockId): FlexBox
    {
        $moreButton = new FlexButton([
            'type' => ComponentType::BUTTON,
            'style' => ComponentButtonStyle::LINK,
            'height' => ComponentButtonHeight::SM,
            'action' => new PostbackAction([
                'type' => ActionType::POSTBACK,
                'label' => '更多資訊',
                'data' => 'action=more&stock_id='. $stockId,
            ]),
        ]);

        return new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::VERTICAL,
            'spacing' => ComponentSpacing::SM,
            'flex' => 0,
            'backgroundColor' => '#fafafa',
            'borderColor' => '#e0e0e0',
            'borderWidth' => '1px',
            'contents' => [$moreButton],
        ]);
    }
}
