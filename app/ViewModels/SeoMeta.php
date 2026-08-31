<?php

namespace App\ViewModels;

final readonly class SeoMeta
{
    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public string $robots = 'index,follow',
    ) {}

    /**
     * @return array{title: string, description: string, canonical: string, robots: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'robots' => $this->robots,
        ];
    }

    /**
     * @param  array{title: string, description: string, canonical: string, robots: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            canonical: $data['canonical'],
            robots: $data['robots'],
        );
    }
}
