<?php
    /**
     * @author  Foma Tuturov <fomiash@yandex.ru>
     */

    use \Phphleb\Updater\Classes\Data;

    if (end($argv) === '--remove') {
        $action = false;
    } else if (end($argv) === '--add') {
        $action = true;
    } else {
        die (
            "\n" . "Sitemap generator." .
            "\n" . "--remove (delete module)" .
            "\n" . "--add    (add/update module)" . "\n"
        );
    }

    include_once __DIR__ . '/../updater/classes/Data.php';

    if ($action) {
        $publicDirectoryName = Data::getEnvironment('siteMapPublicDirectory') ?? sitemapgenSelectPublicDirectory();
        include __DIR__ . "/App/ConfigManager.php";

        if (Data::getEnvironment('siteMapDomain')) {
            $fullSiteDomain = Data::getEnvironment('siteMapDomain');
        } else if (\Phphleb\Sitemapgen\App\ConfigManager::searchConfig()) {
            $fullSiteDomain = \Phphleb\Sitemapgen\App\ConfigManager::getDomainByConfig();
        } else {
            $fullSiteDomain = sitemapgenSelectDomain();
        }
        $addInRobotsTxt = Data::getEnvironment('siteMapInRobots') ?? sitemapgenSelectAddRobotsTxt();

        include __DIR__ . "/add_sitemapgen.php";
    } else {
        include __DIR__ . "/remove_sitemapgen.php";
    }

    function sitemapgenSelectPublicDirectory()
    {
        $dir = realpath(__DIR__ . '/../../../' . trim(readline('Specify the name of the public folder of the project to place `sitemap` (for example `public` or `public_html`) >'), ' \\/'));
        if ($dir && file_exists($dir) && file_exists($dir . DIRECTORY_SEPARATOR . 'index.php')) {
            return $dir;
        }
        return sitemapgenSelectPublicDirectory();
    }

    function sitemapgenSelectDomain()
    {
        $domain = rtrim(readline('Enter the domain name of the site like example `https://phphleb.ru`>'), ' \\/');
        if (strpos($domain, 'http') === 0) {
            return $domain;
        }
        return sitemapgenSelectDomain();
    }

    function sitemapgenSelectAddRobotsTxt() {
        $actionType = readline('Add sitemap.xml link to robots.txt? Enter Y(yes) or N(no)>');
        if ($actionType === "Y") {
            return true;
        }
        if ($actionType === "N") {
            return false;
        }
        return sitemapgenSelectAddRobotsTxt();
    }

