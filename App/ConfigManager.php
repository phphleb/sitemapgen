<?php

declare(strict_types=1);

namespace Phphleb\Sitemapgen\App;

use Phphleb\Nicejson\JsonConverter;
use Phphleb\Sitemapgen\App\Helpers\RobotsTxtHelper;

class ConfigManager
{
    private string $sitemapDirectory;

    private string $viewDirectory;

    private string $storageDirectory;

    private string $domain;

    public function __construct(
        string $sitemapDirectory,
        string $viewDirectory,
        string $storageDirectory,
        string $domain
    )
    {
        $this->sitemapDirectory = $sitemapDirectory;
        $this->viewDirectory = $viewDirectory;
        $this->storageDirectory = $storageDirectory;
        $this->domain = $domain ?? $this->getDomainByConfig();
        if (!$this->domain) {
            throw new \ErrorException("Domain name not found!");
        }
    }

    public function getSitemapDirectory(): string
    {
        return $this->sitemapDirectory;
    }

    public static function searchConfig(): bool
    {
        return file_exists(HLEB_STORAGE_DIRECTORY . '/lib/sitemapgen/config.json');
    }

    public static function getDomainByConfig(): ?string
    {
        $config = json_decode(file_get_contents(HLEB_STORAGE_DIRECTORY . '/lib/sitemapgen/config.json'), true);
        return $config['sitemapgen']['data']['url'] ?? null;
    }

    public static function getDataByConfig(): ?array
    {
        $config = json_decode(file_get_contents(HLEB_STORAGE_DIRECTORY . '/lib/sitemapgen/config.json'), true);
        return $config['sitemapgen'] ?? null;
    }

    public function createBaseSitemap(): void
    {
        (new CompoundXml($this->sitemapDirectory, $this->viewDirectory, $this->domain))
            ->createNewSitemap();
    }

    public function addMapInRobotsTxt(): void
    {
        (new RobotsTxtHelper())->searchAndUpdateMapUrl(rtrim($this->domain, ' \\/') . '/sitemap.xml');
    }

    public function fileRobotsTxtExists(): bool
    {
        return (new RobotsTxtHelper())->searchFile();
    }

    public function copyConfig(): void
    {
        $configDir = $this->storageDirectory . DIRECTORY_SEPARATOR . 'lib';
        if (!file_exists($configDir)) {
            mkdir($configDir);
        }
        $configLib = $configDir . DIRECTORY_SEPARATOR . 'sitemapgen';
        if (!file_exists($configLib)) {
            mkdir($configLib);
        }
        $configFile = $configLib . DIRECTORY_SEPARATOR . 'config.json';
        if (!file_exists($configFile)) {
            $defaultConfig = json_decode(file_get_contents(__DIR__ . '/../default-config.json'), true);
            $defaultConfig['sitemapgen']['data']['url'] = $this->domain;
            $data = (new JsonConverter($defaultConfig))->get();
            file_put_contents($configFile, $data);
        }
    }
}

