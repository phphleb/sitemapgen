<?php

declare(strict_types=1);

namespace Phphleb\Sitemapgen\App\Helpers;

class DataHelper
{
    const MAX_SYMBOLS_IN_URL = 2048;

    const CHANGEFREQ_PARAMS = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    const STR_SEARCH = ['&', '\'', '"', '>', '<'];

    const STR_REPLACE = ['&amp;', '&apos;', '&quot;', '&gt;', '&lt;'];

    const REVERT_SEARCH = ['%3A', '%2F'];

    const REVERT_REPLACE = [':', '/'];

     public function convertUrl(string $url) {
         $url = str_replace(self::STR_SEARCH, self::STR_REPLACE, rawurlencode($url));
         $url = str_replace(self::REVERT_SEARCH, self::REVERT_REPLACE, $url);
         if (strlen($url) > self::MAX_SYMBOLS_IN_URL) {
             return false;
         }
         return $url;
     }

     public function changefreqValidateOrFile(string $value) {
         if (!in_array($value, self::CHANGEFREQ_PARAMS)) {
             throw new \ErrorException('Unsupported changefreq value:' . $value);
         }
         return $value;
     }

     public function getEndingUrl(): bool
     {
         return defined('HLEB_SYSTEM_ENDING_URL') ? HLEB_SYSTEM_ENDING_URL : HLEB_PROJECT_ENDING_URL;
     }
}