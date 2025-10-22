<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class PostDraft
{
    public string $title;
    public string $body;
    public array $attachments;
    public array $refs;

    public function __construct(string $title, string $body, array $attachments, array $refs)
    {
        $this->title = $title;
        $this->body = $body;
        $this->attachments = $attachments;
        $this->refs = $refs;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? '',
            $data['body'] ?? '',
            $data['attachments'] ?? [],
            $data['refs'] ?? []
        );
    }

    public function toMinifiedJson(): string
    {
        $data = [
            'attachments' => $this->attachments,
            'body' => $this->body,
            'refs' => $this->refs,
            'title' => $this->title,
        ];
        // Sort keys to ensure deterministic JSON output
        ksort($data);
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function calculatePostBytesHash(): string
    {
        return hash('sha256', $this->toMinifiedJson(), true);
    }
}
