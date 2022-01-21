<?php

declare(strict_types=1);

namespace Slack;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class SlackMessage
{
    private string $url;

    private string $title;

    private string $text;

    private string $color;

    public const COLOR_GREEN = '#26a354';
    public const COLOR_RED = '#d61817';
    public const COLOR_BLUE = '#4ba1bc';
    public const COLOR_GRAY = '#7e8b8c';

    public const VALID_COLORS = [
        self::COLOR_GREEN,
        self::COLOR_RED,
        self::COLOR_BLUE,
        self::COLOR_GRAY,
    ];

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @param string $color
     * @throws Exception
     */
    public function setColor(string $color): void
    {
        if (!in_array($color, self::VALID_COLORS, true)) {
            throw new Exception('Invalid color: ' . $color);
        }

        $this->color = $color;
    }

    /**
     * @return array
     */
    private function getMessage(): array
    {
        $message = [];

        if (isset($this->title)) {
            $message['title'] = $this->title;
        }

        if (isset($this->color)) {
            $message['color'] = $this->color;
        }

        if (isset($this->text)) {
            $message['text'] = $this->text;
        }

        return $message;
    }

    /**
     * @throws GuzzleException
     */
    public function send()
    {
        $client = new Client();
        $payload = [
            RequestOptions::JSON => [
                'attachments' => [
                    $this->getMessage(),
                ],
            ],
        ];

        $client->post($this->url, $payload);
    }
}
