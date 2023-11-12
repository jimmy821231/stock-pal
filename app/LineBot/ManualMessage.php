<?php
namespace App\LineBot;

use LINE\Clients\MessagingApi\Model\FlexBox;
use LINE\Clients\MessagingApi\Model\FlexBubble;
use LINE\Clients\MessagingApi\Model\FlexComponent;
use LINE\Clients\MessagingApi\Model\FlexImage;
use LINE\Clients\MessagingApi\Model\FlexMessage;
use LINE\Clients\MessagingApi\Model\FlexText;
use LINE\Constants\Flex\BubbleContainerSize;
use LINE\Constants\Flex\ComponentFontSize;
use LINE\Constants\Flex\ComponentFontWeight;
use LINE\Constants\Flex\ComponentImageAspectMode;
use LINE\Constants\Flex\ComponentImageAspectRatio;
use LINE\Constants\Flex\ComponentImageSize;
use LINE\Constants\Flex\ComponentLayout;
use LINE\Constants\Flex\ComponentType;
use LINE\Constants\Flex\ContainerType;
use LINE\Constants\MessageType;

class ManualMessage
{
    public static function get($title, $content): FlexMessage
    {
        return new FlexMessage([
            'type' => MessageType::FLEX,
            'altText' => $title,
            'contents' => new FlexBubble([
                'type' => ContainerType::BUBBLE,
                'hero' => self::createHeroBlock(),
                'body' => self::createBodyBlock($title, $content),
                'size' => BubbleContainerSize::GIGA,
            ])
        ]);
    }

    private static function createHeroBlock(): FlexComponent
    {
        return new FlexImage([
            'type' => ComponentType::IMAGE,
            'url' => 'https://i.imgur.com/VBR00gl_d.webp?maxwidth=760&fidelity=grand',
            'size' => ComponentImageSize::FULL,
            'aspectRatio' => ComponentImageAspectRatio::R20TO13,
            'aspectMode' => ComponentImageAspectMode::COVER,
        ]);
    }

    private static function createBodyBlock($title, $content): FlexBox
    {
        return new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::VERTICAL,
            'backgroundColor' => '#fafafa',
            'paddingAll' => '8%',
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $title,
                    'weight' => ComponentFontWeight::BOLD,
                    'size' => ComponentFontSize::XL,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $content,
                    'wrap' => true,
                    'size' => ComponentFontSize::SM,
                ]),
            ],
        ]);
    }
}
