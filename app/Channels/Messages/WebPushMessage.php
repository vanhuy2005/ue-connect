<?php

namespace App\Channels\Messages;

class WebPushMessage
{
    protected string $title = 'UE Connect';

    protected string $body = '';

    protected ?string $icon = '/images/icons/icon-192.png';

    protected ?string $badge = '/images/icons/badge-72.png';

    protected ?string $url = null;

    protected ?string $tag = null;

    protected ?string $type = null;

    protected ?string $category = null;

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function badge(?string $badge): self
    {
        $this->badge = $badge;

        return $this;
    }

    public function url(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function tag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function type(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function category(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'url' => $this->url,
            'tag' => $this->tag,
            'type' => $this->type,
        ];
    }
}
