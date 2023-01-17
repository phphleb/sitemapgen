<?php
/**
 * Пример класса для генерации частей url c двумя вариантами:
 *
 *  В первом все части url находятся последовательно,
 *  во втором массив с каждой частью, с первой, второй и тд.
 * 
 *  Количество частей во всех частях каждого варианта должно быть одинаковым.
 */

namespace Phphleb\Sitemapgen\App;


class TestGenerator
{
     public function getV1() {
         return [
             ["url_part_1" => 10, 'url_part_2' => 20, 'url_part_3' => 'a30'],
             ["url_part_1" => 11, 'url_part_2' => 21, 'url_part_3' => 'a31'],
             ["url_part_1" => 12, 'url_part_2' => 22, 'url_part_3' => 'a32'],
             ["url_part_1" => 13, 'url_part_2' => 23, 'url_part_3' => 'a33'],
             ["url_part_1" => 14, 'url_part_2' => 24, 'url_part_3' => 'a34'],
             ["url_part_1" => 15, 'url_part_2' => 25, 'url_part_3' => 'a35'],
         ];
     }

    public function getV2UrlPart1() {
        return [ 10, 11, 12, 13, 14, 15 ];
    }

    public function getV2UrlPart2() {
        return [ 20, 21, 22, 23, 24, 25 ];
    }

    public function getV2UrlPart3() {
        return [ 'a30', 'a31', 'a32', 'a33', 'a34', 'a35' ];
    }
}

