<?php
    /**
     * @author  Foma Tuturov <fomiash@yandex.ru>
     *
     * Adding sitemap generation to the project.
     * Добавление генерации карты сайта в проект.
     */

    include_once __DIR__ . "/loader.php";
    include_once __DIR__ . "/../updater/FileUploader.php";

    if (!defined('HLEB_PUBLIC_DIR') || !realpath(HLEB_PUBLIC_DIR) || realpath(HLEB_PUBLIC_DIR) !== realpath($publicDirectoryName)) {
       die('In the `console` file, the path to the public folder is incorrectly specified in the HLEB_PUBLIC_DIR constant. For example define(\'HLEB_PUBLIC_DIR\', realpath(HLEB_GLOBAL_DIRECTORY . \'/public\'));' . PHP_EOL . PHP_EOL);
    }

    $designPatterns = ['base'];

    $uploader = new \Phphleb\Updater\FileUploader(__DIR__ . DIRECTORY_SEPARATOR . "hleb-project-relationship");

    $uploader->setDesign($designPatterns);

    $uploader->setPluginNamespace(__DIR__, 'Sitemapgen');

    $uploader->setSpecialNames('sitemapgen', 'Sitemapgen');

    $uploader->run();

    $config = new \Phphleb\Sitemapgen\App\ConfigManager(
        HLEB_PUBLIC_DIR,
        HLEB_GLOBAL_DIRECTORY . '/resources/views',
        HLEB_STORAGE_DIRECTORY,
        $fullSiteDomain
    );
    $config->copyConfig();
    $config->createBaseSitemap();
    if ($addInRobotsTxt) {
        if ($config->fileRobotsTxtExists()) {
            $config->addMapInRobotsTxt();
        } else {
            echo PHP_EOL . '`robots.txt` file not found!' . PHP_EOL;
        }
    }

    echo PHP_EOL . 'Success! The file `/sitemapgen/sitemap-reserve.xml` has been created. It contains the static URLs of the site. The dynamic ones will be generated automatically from the `/storage/lib/sitemapgen/config.json` configuration.' . PHP_EOL . PHP_EOL;

