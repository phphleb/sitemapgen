 ## SITEMAP Generator from HLEB framework

_Hope you don't need it_ 😀
 
### Installation
Step 1. Installation via Composer into an existing HLEB project:
 ```bash
 $ composer require phphleb/sitemapgen
 ```

Step 2. Installing the library into the project.

 ```bash
 $ php console phphleb/sitemapgen --add
 ```

 ```bash
 $ composer dump-autoload
 ```

### Configuration

Step 3. Modify the configuration according to the sample `/storage/lib/sitemapgen/config.json`. 
You must create classes that the handler can call on to get the data it needs.

For static (unchangeable) addresses, there is a file `/public/sitemapgen/sitemap-reserve.xml`.
You must enter this information manually.


Sample routes for generating a test sitemap:
```php
Route::get('/variant-one/preview/{url_part_1}/test/{url_part_2}/{url_part_3?}', 'view sitemap_test_1_route_name')->name('sitemap_test_1_route_name');
Route::get('/variant-two/preview/{url_part_1}/test/{url_part_2}/{url_part_3?}', 'view sitemap_test_1_route_name')->name('sitemap_test_2_route_name');
```


### Generation
Step 4. Run task:

 ```bash
 $ php console sitemapgen/update-sitemap-task
 ```


-----------------------------------

[![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/draft/blob/main/LICENSE) ![PHP](https://img.shields.io/badge/PHP-^7.4.0-blue) ![PHP](https://img.shields.io/badge/PHP-8-blue)


