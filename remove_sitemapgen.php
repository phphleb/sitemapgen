<?php
    /**
     * @author  Foma Tuturov <fomiash@yandex.ru>
     *
     * Removing the sitemap generation from the project.
     * Удаление генерации карты сайта из проекта.
     */

    include_once __DIR__ . "/loader.php";
    include_once __DIR__ . "/../updater/FileRemover.php";

    $remover = new \Phphleb\Updater\FileRemover(__DIR__ . DIRECTORY_SEPARATOR);

    $remover->setSpecialNames('sitemapgen', 'Sitemapgen');

    $remover->run();

    echo PHP_EOL;

