<?php

namespace App\Services\Ai;

interface Embedder
{
    /**
     * Returns one embedding vector per input string, in the same order.
     *
     * @param  array<int, string>  $inputs
     * @return array<int, array<int, float>>
     */
    public function embed(array $inputs): array;
}
