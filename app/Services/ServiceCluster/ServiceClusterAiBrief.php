<?php

namespace App\Services\ServiceCluster;

final readonly class ServiceClusterAiBrief
{
    public function __construct(
        public int $serviceId,
        public string $topic,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'service_id' => $this->serviceId,
            'topic' => $this->topic,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceId: self::intFrom($data['service_id'] ?? null),
            topic: is_string($data['topic'] ?? null) ? $data['topic'] : '',
        );
    }

    private static function intFrom(mixed $value): int
    {
        return match (true) {
            is_int($value) => $value,
            is_string($value), is_float($value) => (int) $value,
            default => 0,
        };
    }
}
