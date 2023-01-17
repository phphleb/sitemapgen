<?php

declare(strict_types=1);

namespace Phphleb\Sitemapgen\App\Helpers;

class RobotsTxtHelper
{
    public const ROBOTS_TXT_FILE = HLEB_PUBLIC_DIR . DIRECTORY_SEPARATOR . 'robots.txt';

    public function searchFile(): bool
    {
        return file_exists(self::ROBOTS_TXT_FILE);
    }

    public function searchAndUpdateMapUrl(string $url)
    {
        $file = $this->getFileRows();
        $message = '# This line was added automatically when installing the phphleb/sitemapgen library';
        $search = false;
        $replace = false;
        $origin = "Sitemap: $url" . PHP_EOL;
        foreach($file as $key => $row) {
            if (strpos($row, 'Sitemap') !== false && strpos($row, trim($url, ' \\/')) !== false) {
                $search = true;
                if ($row !== $origin) {
                    $replace = true;
                    $file[$key] = $origin;
                }
            }
        }
        if (!$search) {
            $file[] = PHP_EOL . $message . PHP_EOL;
            $file[] = $origin;

            file_put_contents(self::ROBOTS_TXT_FILE, implode($file));
            return true;
        }
        if ($replace) {
            file_put_contents(self::ROBOTS_TXT_FILE, implode($file));
            return true;
        }

        return false;
    }

    /**
     * Возвращает массив построчно с текущего файла robots.txt
     * или false, если файл не найден.
     *
     * @return array|false
     */
    public function getFileRows(): ?array
    {
        $text = fopen(self::ROBOTS_TXT_FILE, "r");
        $result = [];
        if ($text) {
            while (($buffer = fgets($text)) !== false) {
                $result[] = $buffer;
            }
            fclose($text);

            return $result;
        }

        return null;
    }
}

