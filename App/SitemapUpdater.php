<?php

declare(strict_types=1);

namespace Phphleb\Sitemapgen\App;

use Hleb\Constructor\Cache\CacheRoutes;
use Hleb\Constructor\Handlers\URL;
use Hleb\Main\Console\MainConsole;
use Phphleb\Sitemapgen\App\Helpers\DataHelper;
use Phphleb\sitemapgen\App\Helpers\RemoveDirectoryHelper;

class SitemapUpdater
{
    const MAXIMUM_ROWS_IN_FILE = 10000;

    private array $configSrc;

    // Базовый url из конфигурации
    private string $url;

    private string $changefreq;

    private float $priority;

    private string $lastmod;

    private array $routeNames;

    private string $sitemapDirectory = HLEB_PUBLIC_DIR;

    // Порядковый номер сохраняемой строки
    private static int $counter = 0;

    private static array $lastMap = [];

    private static ?string $lastRouteName = null;

    private static ?string $lastRouteDomain = null;

    private static int $fileNumber = 1;

    private string $generatedDir;

    private RemoveDirectoryHelper $removeHelper;

    private array $newXmlFiles = [];

    private DataHelper $dataHelper;

    private string $viewDir;

    public function __construct()
    {
        if (!file_exists(HLEB_GLOBAL_DIRECTORY . '/storage/cache/routes/routes.txt')) {
            die(PHP_EOL . 'Route cache not found! Execute the command `php console --update-routes-cache`' . PHP_EOL);
        }
        if (!file_exists(HLEB_PUBLIC_DIR)) {
            die(PHP_EOL . 'Wrong public directory specified in `console` file!' . PHP_EOL);
        }
        $config = ConfigManager::getDataByConfig();
        $this->configSrc = $config['src'];
        $this->url = $config['data']['url'];
        $this->changefreq = $config['data']['changefreq'] ?? 'monthly';
        $this->priority = $config['data']['priority'] ?? 0.8;
        $this->lastmod = $config['data']['lastmod'] ?? date('Y-m-d');
        $this->routeNames = (new RouteParser())->get();
        $this->removeHelper = new RemoveDirectoryHelper();
        $this->dataHelper = new DataHelper();
        $this->generatedDir = HLEB_PUBLIC_DIR . DIRECTORY_SEPARATOR . 'sitemapgen' . DIRECTORY_SEPARATOR . 'generated';
        $this->viewDir = HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sitemapgen' . DIRECTORY_SEPARATOR;
        // Доступ к карте маршрутов.
        try {
            (new MainConsole([]))->generateRouteCache();
            $routesArray = (new CacheRoutes())->load();
            if (isset($routesArray['addresses'])) URL::create($routesArray['addresses']);
        } catch (\Throwable $e) {
            throw new \ErrorException('Route cache not found! Execute the command `php console --update-routes-cache`. Then restart the current command. ERROR: ' . $e->getMessage());
        }
    }

    public function run()
    {
        $data = ConfigManager::getDataByConfig();

        if (!$data) {
            throw new \ErrorException("Configuration file not found. Execute `php console phphleb/sitemapgen --add`");
        }
        $this->removeHelper->removeFiles($this->generatedDir);
        $this->removeHelper->revertGeneratedFile();

        if (empty($data['src'])) {
            return "No conversion settings found in config file. Sitemap updated.";
        }
        // Сортировка по доменам
        $src = $this->sortByDomain($data['src']);
        foreach ($src as $key => $item) {
            $this->sitemapIteration($item);
        }
        $this->saveData();
        $this->saveXmlLinks();
    }

    /**
     * Перебираются динамические роуты из массива по имени маршрута.
     */
    private function sitemapIteration(array $params)
    {
        if (!isset($params['routeName']) || !in_array($params['routeName'], $this->routeNames)) {
            throw new \ErrorException("The route name " . $params['routeName'] . " was not found in the route map.");
        }
        $domain = empty($params['url']) ? $this->url : $params['url'];
        if (self::$lastRouteDomain !== $domain) {
            self::$lastRouteDomain = $domain;
            $this->saveData();
        }
        $source = $params['source'];
        unset($params['source']);
        /**
         * Определяется, что подан список частей из первого варианта.
         * @see \Phphleb\Sitemapgen\App\TestGenerator
         */
        if (count($source) === 2 && is_string($source[0]) && is_string($source[1])) {
            $class = $source[0];
            $method = $source[1];
            $rows = (new $class)->$method();
            if (!is_array($rows)) {
                throw new \ErrorException(" The return value of the method $class:{$source[1]} must be an array!");
            }
            $this->generateMap($rows, $params, $domain);
        } else {
            // Второй вариант.
            $data = [];
            $count = null;
            // Перебор source из конфига.
            foreach ($source as $partName => $value) {
                $class = $value[0];
                $method = $value[1];
                $rows = (new $class)->$method();
                if (!is_array($rows)) {
                    throw new \ErrorException(" The return value of the method $class:{$value[1]} must be an array!");
                }
                $rows = array_values($rows);
                if (is_null($count) || $count < count($rows)) {
                    $count = count($rows);
                }
                // Последний элемент может отсутствовать в маршруте.
                if ($count != count($rows) && $count != (count($rows) - 1)) {
                    throw new \ErrorException("Wrong number of parts in resulting array from $class:{$value[1]}.");
                }
                // Сборка стандартного массива по номерам частей.
                $data[$partName] = $rows;
            }
            $result = [];
            $dataKeys = array_keys($data);
            for ($i = 0; $i < $count; $i++) {
                $list = [];
                foreach ($dataKeys as $key) {
                    if (isset($data[$key][$i])) {
                        $list[$key] = $data[$key][$i];
                    }
                }
                $result[] = $list;
            }
            $this->generateMap($result, $params, $domain);
        }

        self::$lastRouteName = $params['routeName'];
    }

    /**
     * Сохранение набранного массива строк @see self::$lastMap с вариантами url.
     */
    private function saveData()
    {
        if (empty(self::$lastMap)) {
            self::$counter = 0;
            return;
        }
        // Новый файл
        $this->fileNumber++;
        $dir = $this->generatedDir . DIRECTORY_SEPARATOR . $id;
        $file = $dir . DIRECTORY_SEPARATOR . 'sm-' . $this->fileNumber . '.xml';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        if (!file_exists($file)) {
            $content = file_put_contents($file, file_get_contents($this->viewDir . 'xml-header.php') . PHP_EOL);
            $this->newXmlFiles[$this->fileNumber] = [
                'loc' => trim($this->url, ' \\/') . '/sitemapgen/generated/sm-' . $this->fileNumber . '.xml',
                'lastmod' => "0"
            ];
        }
        $resource = fopen($file, 'a');
        $template = file_get_contents($this->viewDir . 'xml-content.php');
        foreach (self::$lastMap as $item) {
            $lastmod = $item['params']['lastmod'] ?? $this->lastmod;
            $params = array_values([
                'loc' => $item['loc'],
                'lastmod' => $item['params']['lastmod'] ?? $this->lastmod,
                'changefreq' => $item['params']['changefreq'] ?? $this->changefreq,
                'priority' => (float)($item['params']['priority'] ?? $this->priority),
            ]);
            $content = trim(sprintf($template, ...$params), '\n\r\t\v\x00') . PHP_EOL;
            // Сохранение строки в файл
            fputs($resource, $content);

            if (strtotime((string)$this->newXmlFiles[$this->fileNumber]['lastmod']) < strtotime($lastmod)) {
                $this->newXmlFiles[$this->fileNumber]['lastmod'] = $lastmod;
            }
        }
        fputs($resource, file_get_contents($this->viewDir . 'xml-footer.php'));
        fclose($resource);

        // Обнуление массива строк для сохранения.
        self::$counter = 0;
        self::$lastMap = [];
    }

    // Сортировка массива по вложенным доменам
    private function sortByDomain(array $data): array
    {
        $result = [];
        $items = [];
        foreach ($data as $item) {
            $domain = $item['url'] ?? 0;
            $items[$domain][] = $item;
        }
        rsort($items, SORT_STRING);
        foreach ($items as $item) {
            foreach ($item as $row) {
                $result[] = $row;
            }
        }
        return $result;
    }

    private function generateMap(array $rows, array $params, string $domain)
    {
        /** @var  $param - массив с параметрами для подстановки в именованный маршрут. */
        foreach ($rows as $param) {
            if (self::$counter >= self::MAXIMUM_ROWS_IN_FILE) {
                $this->saveData();
            }
            self::$counter++;
            // Получение url
            $pageUrl = rtrim($domain, '\\/') . '/' . $this->dataHelper->convertUrl(ltrim(\Hleb\Constructor\Handlers\URL::getByName($params['routeName'], $param), '\\/'));
            self::$lastMap[] = ['loc' => $pageUrl, 'params' => $params];
        }
    }

    private function saveXmlLinks()
    {
        $file = HLEB_PUBLIC_DIR . '/sitemapgen/sitemap-generated.xml';
        file_put_contents($file, file_get_contents($this->viewDir . 'xml-list-header.php') . PHP_EOL);

        $resource = fopen($file, 'a');
        $template = file_get_contents($this->viewDir . 'xml-list-content.php');
        foreach ($this->newXmlFiles as $link) {
            $params = array_values([
                'loc' => $link['loc'],
                'lastmod' => $link['lastmod'],
            ]);
            $content = trim(sprintf($template, ...$params), '\n\r\t\v\x00') . PHP_EOL;
            fputs($resource, $content);

        }
        fputs($resource, file_get_contents($this->viewDir . 'xml-list-footer.php'));
        fclose($resource);
    }
}

