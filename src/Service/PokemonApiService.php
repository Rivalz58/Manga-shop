<?php

namespace App\Service;

/**
 * Service d'intégration externe : consomme l'API PokeAPI via cURL.
 * Intégration CURL obligatoire selon l'énoncé.
 */
class PokemonApiService
{
    private const BASE_URL = 'https://pokeapi.co/api/v2';
    private const CACHE_TTL = 3600;

    private array $cache = [];

    public function getPokemonInfo(string $nomOuId): ?array
    {
        $cacheKey = 'pokemon_' . $nomOuId;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $data = $this->fetch('/pokemon/' . strtolower($nomOuId));
        if ($data) {
            $this->cache[$cacheKey] = $data;
        }
        return $data;
    }

    public function getPokemonSprite(string $nomOuId): ?string
    {
        $info = $this->getPokemonInfo($nomOuId);
        if (!$info) return null;

        return $info['sprites']['other']['official-artwork']['front_default']
            ?? $info['sprites']['front_default']
            ?? null;
    }

    public function getPokemonTypes(string $nomOuId): array
    {
        $info = $this->getPokemonInfo($nomOuId);
        if (!$info) return [];

        return array_map(
            fn($t) => ucfirst($t['type']['name']),
            $info['types'] ?? []
        );
    }

    public function getPokemonBaseStats(string $nomOuId): array
    {
        $info = $this->getPokemonInfo($nomOuId);
        if (!$info) return [];

        $stats = [];
        foreach ($info['stats'] ?? [] as $stat) {
            $stats[$stat['stat']['name']] = $stat['base_stat'];
        }
        return $stats;
    }

    public function getListePokemon(int $limit = 20, int $offset = 0): array
    {
        $data = $this->fetch("/pokemon?limit={$limit}&offset={$offset}");
        return $data['results'] ?? [];
    }

    private function fetch(string $endpoint): ?array
    {
        $url = self::BASE_URL . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'PokemonShop/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }
}
