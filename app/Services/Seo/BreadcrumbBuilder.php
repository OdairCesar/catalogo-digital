<?php

namespace App\Services\Seo;

final class BreadcrumbBuilder
{
    /** @var array<int, array{label: string, url?: string}> */
    private array $items;

    private function __construct()
    {
        $this->items = [
            ['label' => 'Início', 'url' => route('home')],
        ];
    }

    public static function start(): self
    {
        return new self;
    }

    public function add(string $label, ?string $url = null): self
    {
        $this->items[] = $url !== null ? ['label' => $label, 'url' => $url] : ['label' => $label];

        return $this;
    }

    /**
     * @return array<int, array{label: string, url?: string}>
     */
    public function build(): array
    {
        return $this->items;
    }
}
