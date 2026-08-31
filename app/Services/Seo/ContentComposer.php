<?php

namespace App\Services\Seo;

use App\Models\City;

final class ContentComposer
{
    public function compose(string $template, ?City $city = null): string
    {
        return strtr($template, $this->tokens($city));
    }

    public function hasLocationTokens(string $template): bool
    {
        return str_contains($template, '{cidade}') || str_contains($template, '{uf}') || str_contains($template, '{regiao}');
    }

    /**
     * @param  array<int, string>  $benefits
     * @return array<int, string>
     */
    public function composeList(array $benefits, ?City $city = null): array
    {
        return array_map(
            fn (string $benefit): string => $this->compose($benefit, $city),
            $benefits,
        );
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @return array<int, array{question: string, answer: string}>
     */
    public function composeFaq(array $faq, ?City $city = null): array
    {
        return array_map(fn (array $item): array => [
            'question' => $this->compose($item['question'], $city),
            'answer' => $this->compose($item['answer'], $city),
        ], $faq);
    }

    /**
     * @return array<string, string>
     */
    private function tokens(?City $city): array
    {
        if ($city === null) {
            return [
                '{cidade}' => 'sua cidade',
                '{uf}' => 'sua região',
                '{regiao}' => 'sua região',
            ];
        }

        $state = $city->state;

        return [
            '{cidade}' => $city->name,
            '{uf}' => $state !== null ? $state->uf : '',
            '{regiao}' => $city->region,
        ];
    }
}
