<?php

namespace App\Http\Controllers;

use App\Models\CatPageParsing;
use Illuminate\Http\Request;

use App\Http\Controllers\LoaderController;
use App\Models\Good;
use App\Models\Page;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

use Symfony\Component\DomCrawler\Crawler;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

use App\Http\Controllers\CatController;

// // namespace Facebook\WebDriver;
// use Facebook\WebDriver\Remote\DesiredCapabilities;
// use Facebook\WebDriver\Remote\RemoteWebDriver;
// // require_once('vendor/autoload.php');

class GoodController extends Controller
{

    // куда сохраняем картинку в сторадж
    static public $folderToImg = '/data_za/gallery/';

    /**
     * парсинг страниц товаров ( тащим загруженные и отправляем на парсинг )
     *
     * @return \Illuminate\Http\Response
     */
    public function parsingGoods()
    {

        // $html = <<<'HTML'
        // <!DOCTYPE html>
        // <html>
        //     <body>
        //         <p class="message">Hello World!</p>
        //         <p>Hello Crawler!</p>
        //     </body>
        // </html>
        // HTML;
        //  
        // $crawler = new Crawler($html);
        //  
        // foreach ($crawler as $domElement) {
        //     var_dump($domElement->nodeName);
        // }

        // $res = [];

        $goods = Good::where('load-type', 'loaded')->limit(1)->get();
        // // dd([__FILE__, __LINE__,$goods]);
        foreach ($goods as $good) {
            $goodPage = Page::where('uri', 'https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true')->limit(1)->get();
            echo '<br/><br/>' . '<a href="' . ('https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true') . '" >' . 'https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true' . '</a>';
            $r = self::parsingGoodsFromHtml($goodPage[0]->html, $good->uri);
            echo '<pre>', print_r($r), '</pre>';
            // echo '<pre>', htmlspecialchars($html0), '</pre>';
        }
    }

    public function parsingGoodsFromHtml($html, $goodUri = '')
    {

        $crawler = new Crawler($html);

        $ar = [];

        // $ar['cats'] = self::parsingGoodCats($crawler);
        $ar['cats'] = CatController::parsingGoodCats($crawler);
        $ar['cats-save'] = CatController::saveCats($ar['cats']);
        // dd(__FILE__, __LINE__, $ar['cats-save']);

        $ar['good'] = self::parsingGood($crawler);
        $ar['prop'] = self::parsingGoodProperies($crawler);

        $ar['good']['cat_id'] = $ar['cats-save']['nowCat'];

        $ar['good']['img'] = self::parsingGoodSaveImg($goodUri, $ar['good']['imgOrigin'] ?? '');

        // dd($ar);

        return $ar;
    }

    /**
     * тащим из страницы товара инфу что есть о товаре
     */
    public function parsingGoodSaveImg($goodUri, $ImgLink)
    {
        $ar = [];

        $info = new \SplFileInfo($ImgLink);
        // var_dump($info->getExtension());

        $newName = $goodUri . '.' . $info->getExtension();
        $newImg = '/public' . self::$folderToImg . $newName;

        if (Storage::exists($newImg)) {
            $newName = $goodUri . '.' . rand(100, 999)  .  '.' . $info->getExtension();
            $newImg = '/public' . self::$folderToImg . $newName;
        }

        // if (Storage::exists($newImg)) {
        //     $inGood2['dop-image'] = 'est';
        // } else {
        Storage::put($newImg, file_get_contents($ImgLink));
        if (Storage::exists($newImg)) {
            return $newName;
            // $inGood2['dop-image'] = 'copyed';
        } else {
            // $inGood2['dop-image'] = 'not copyed';
            return false;
        }
        // }

        // return $ar;
    }

    /**
     * тащим из страницы товара инфу что есть о товаре
     */
    public function parsingGood($crawler)
    {
        $g = [];

        try {
            $g['name'] = $crawler->filter('h1[itemprop=name]')->text();
        } catch (\Throwable $th) {
        }


        try {
            $g['imgOrigin'] = $crawler->filter('a[itemprop=image]')->attr('href');

            // if( !empty( $g['img'] ) ){
            //     file_put_contents(  file_get_contents($g['img'])

            //     // if (Storage::exists($newImg)) {
            //     //     $inGood2['dop-image'] = 'est';
            //     // } else {
            //     //     Storage::put('/public/data_za/gallery/' . $goodUri . '.' . $extension, file_get_contents($inGood2['img']));
            //     //     if (Storage::exists($newImg)) {
            //     //         $inGood2['dop-image'] = 'copyed';
            //     //     } else {
            //     //         $inGood2['dop-image'] = 'not copyed';
            //     //     }
            //     // }

            // }

        } catch (\Throwable $th) {
        }


        try {
            $g['opis'] = $crawler->filter('div[itemprop=description]')->text();
        } catch (\Throwable $th) {
        }
        try {
            $g['articul'] = $crawler->filter('div.sku')->eq(1)->filter('strong')->text();
        } catch (\Throwable $th) {
        }
        try {
            $g['kod'] = $crawler->filter('div.sku')->eq(0)->filter('strong')->text();
        } catch (\Throwable $th) {
        }
        try {
            $g['brand'] = $crawler->filter('a[itemprop=brand]')->text();
        } catch (\Throwable $th) {
        }
        try {
            $g['price'] = preg_replace("/[^,.0-9]/", '',  $crawler->filter('div.price')->text());
        } catch (\Throwable $th) {
        }
        try {
            $g['price-old'] = preg_replace("/[^,.0-9]/", '', $crawler->filter('div.old-price')->text());
        } catch (\Throwable $th) {
        }
        try {
            $g['discount'] = $crawler->filter('div.discount')->text();
        } catch (\Throwable $th) {
        }


        // // $g['kod'] = $crawler->filter('h1[itemprop=name]')->uri();
        // // echo '<Br/>'.__LINE__.' '.$e->text();


        // // тех параметры
        // // try {
        // $pr = $crawler->filter('div.properties-wrap .property');
        // // echo '<pre>'; print_r($pr); echo '</pre>';

        // foreach ($pr as $pr1) {
        //     $pr2 = $pr1->filter('div');
        //     echo '<br/>';
        //     echo '<br/>'.$pr2->text();
        // }

        // // $g['discount'] = $crawler->filter('div.properties-wrap')->text();
        // // $g['properti'] = [];
        // // foreach ($pr as $p) {
        // //     $pr1 = $p->filter('div>span')->text();
        // //     $pr2 = $p->filter('div')->eq(1)->text();
        // //     $g['properti'][] = [
        // //         'name' => $pr1,
        // //         'value' => $pr2,
        // //     ];
        // // }
        // // } catch (\Throwable $th) {
        // // }

        return $g;
    }

    /**
     * тащим из страницы товара инфу что есть о товаре ( параметры и свойства)
     */
    public function parsingGoodProperies($crawler)
    {

        try {
            $g =
                $props = $crawler->filter('div.properties-wrap .property')->each(function (Crawler $node, $i) {
                    return [
                        'name' => $node->filter('div')->eq(1)->text(),
                        'value' => $node->filter('div')->eq(2)->text(),
                    ];
                    // return $node->filter('div')image()->getUri();
                });
        } catch (\Throwable $th) {
            $g = [];
        }

        return $g;
    }

    public function parsingGoodMagazines($crawler)
    {
    }

    /**
     * парсинг страниц товаров ( тащим загруженные и отправляем на парсинг )
     *
     * @return \Illuminate\Http\Response
     */
    public function parsingGoods1()
    {
        $res = [];

        $goods = Good::where('load-type', 'loaded')->limit(1)->get();
        // dd([__FILE__, __LINE__,$goods]);
        foreach ($goods as $good) {

            // dd( $good );
            // dd([__FILE__, __LINE__]);
            $goodPage = Page::where('uri', 'https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true')->limit(1)->get();
            echo '<br/><br/>' . '<a href="' . ('https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true') . '" >' . 'https://zakrepi.ru/catalog/' . $good->uri . '?isAjax=true' . '</a>';
            $goodOld = $good->toArray();

            // dd( [ __FILE__, __LINE__, $goodOld , $goodPage[0] ] );

            // foreach ($goodPage as $page) {

            // dd( $page->html );
            if (!function_exists('str_get_html'))
                include($_SERVER['DOCUMENT_ROOT'] . '/resources/php/simplehtmldom_1_9_1/simple_html_dom.php');

            // echo '<pre>', htmlspecialchars($goodPage[0]->html), '</pre>';

            // $html = str_get_html($content);
            // dd([__FILE__, __LINE__]);
            // $html0 = str_replace('<head/>', '', $goodPage[0]->html);
            $html0 = $goodPage[0]->html;
            // echo '<pre>', htmlspecialchars($html0), '</pre>';
            // dd([__FILE__, __LINE__]);
            $html = str_get_html($html0);


            // dd([__FILE__, __LINE__]);
            $r = self::parsingGoodFullPage($html, $good->uri);
            dd([__FILE__, __LINE__, $r, 'end parsingGoodFullPage']);

            $r['good']['name'] = $good->name;
            // dd($r);

            $r1 = self::saveNewGood($good->uri, $r['good']);

            // dd([__FILE__, __LINE__, '$r' => $r, '$r1' => $r1]);
            //     break;
            // }

            // break;
        }

        return $res;

        // dd( [ __FILE__ , __LINE__ ] );
    }

    public function saveNewGood($uri, $data = [])
    {

        $res = Good::where('uri', $uri)->delete();

        if (!empty($data['cat-id']))
            $res2 = Good::insertGetId($data);

        // dd([$res, $res2 ?? 'x', $data]);
        return [$res, $res2 ?? []];

        // dd( [ __FILE__ , __LINE__ ] );
    }

    public function parsingGoodFullPage($html, $goodUri = '')
    {

        try {

            if (!empty($goodUri)) {

                $inGood2 = [];


                $e = CatController::getOnCatPage_CatsList($html);
                $eSave = CatController::saveCats($e['cats']);
                // $eSave['linkInDb'] = CatController::saveCats($e['cats']);
                // dd($eSave['linkInDb']);

                $inGood = [
                    'cat-id' => $eSave['linkInDb'][$e['last_cat']]['id']
                ];




                // try {
                // описание
                $opis = $html->find('#description [itemprop=description]')[0];
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
                // $e = $opis->find('.product-disclaimer');
                // $e[0]->outertext = '';
                $inGood['opis'] = nl2br(trim($opis->innertext));

                // в каких магазинах сколько
                // try {
                $inGood2['mags'] = self::parsingGoodFullPageGetMagazines($html);
                // dd($inGood2['mags']);
                // } catch (\Exception  $e) {
                //     //throw $th;
                //     echo __LINE__ . ' ' . $e->getMessage();
                // } 
                // catch (\Throwable $th) {
                //     //throw $th;
                //     echo __LINE__ . ' ' . $th->getMessage();
                // }





                // тех параметры выделенные
                try {
                    $tech = $html->find('.properties-wrap')[0];
                } catch (\Throwable $th) {
                    //throw $th;
                }
                $inGood2['tech'] = [];
                if (isset($tech)) {
                    foreach ($tech->find('.property') as $t) {

                        $in = [
                            'param' => trim($t->find('div span')[0]->plaintext)
                        ];

                        if (
                            strpos($in['param'], ', кг')
                            || strpos($in['param'], ', м')
                            || strpos($in['param'], ', шт')
                        ) {
                            $in['value-int'] = preg_replace("/[^,.0-9]/", '', $t->find('div')[1]->plaintext);
                        } else {
                            $in['value'] = trim($t->find('div')[1]->plaintext);
                        }

                        $inGood2['tech'][] = $in;

                        // echo
                        // '<br/>' .
                        //     '<br/>' .
                        //     $t;
                    }
                }

                // артикул
                // try {
                $inGood['articul'] = $html->find('.sku-brand-wrap .sku strong')[0]->plaintext;
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }

                // код
                // try {
                $inGood['kod'] = $html->find('.sku-brand-wrap .sku strong')[1]->plaintext;
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }

                // бренд
                // try {
                $inGood['brand'] = trim($html->find('[itemprop=brand]')[0]->plaintext);
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }

                // картинка
                // try {
                $img0 = $html->find('a[itemprop=image]');
                $inGood2['img'] = $img0[0]->href;

                // srcset="https://zakrepi.ru/images/uploaded/catalog-gallery/strop-kostochka-dlya-evakuatora-60mm-03m/22-11-10-11-13-1_small.jpg 256w, https://zakrepi.ru/images/uploaded/catalog-gallery/strop-kostochka-dlya-evakuatora-60mm-03m/22-11-10-11-13-1_medium.jpg 512w, https://zakrepi.ru/images/uploaded/catalog-gallery/strop-kostochka-dlya-evakuatora-60mm-03m/22-11-10-11-13-1_large.jpg 1200w"
                // $inGood['img'] = $img0->srcset ?? '';

                // file_put_contents( '' file_get_contents()
                // $extension = $file->extension();

                $extension = $ext = pathinfo($inGood2['img'], PATHINFO_EXTENSION);
                $newImg = '/public/data_za/gallery/' . $goodUri . '.' . $extension;
                if (Storage::exists($newImg)) {
                    $inGood2['dop-image'] = 'est';
                } else {
                    Storage::put('/public/data_za/gallery/' . $goodUri . '.' . $extension, file_get_contents($inGood2['img']));
                    if (Storage::exists($newImg)) {
                        $inGood2['dop-image'] = 'copyed';
                    } else {
                        $inGood2['dop-image'] = 'not copyed';
                    }
                }
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }

                // сохраняем товар

                // echo '<br/>' . __LINE__;
                // echo '<pre>', print_r($inGood), '</pre>';
                // echo '<pre>', print_r($inGood2), '</pre>';
                // echo '<br/>' . __LINE__;

                return ['good' => $inGood, 'dop' => $inGood2];
                // } catch (\Exception $e) {
                //     //throw $th;
                //     return [$e->getMessage(), $e,  'error', false];
            }
        } catch (\Exception  $e) {
            //throw $th;
            dd($e);
            echo __LINE__ . ' ' . $e->getMessage();
        }

        dd([
            __FILE__,
            __LINE__,
            'error',
            // $e ?? '' , 
            // '$eSave' => $eSave ,
            // 'linkInDb' => $eSave['linkInDb'] ,
            // 'cat_good' => $eSave['linkInDb'][$e['last_cat']]['id'], 
            // 'in-good' => $inGood
        ]);
    }

    /**
     * загрузка страниц товаров 
     *
     * @return \Illuminate\Http\Response
     */
    public function parsingGoodFullPageGetMagazines($html)
    {

        // $e = $html->find('.shop-in-list');

        $e = $html->find('div.shop-in-list');

        // $e = $html->find('#.shop-list .list-wrap .list .shop-in-list');
        // $e = $html->find('#.shop-list .list-wrap .list .shop-in-list');
        // $e = $html->find('.shop-list .list-wrap .list .shop-in-list');
        // $e2 = $e->find('.shop-list .list-wrap .list .shop-in-list');
        // $e = $html->find('div.shop-in-list');
        // $e = $html->find('div.list');
        // dd($e);
        // echo '<pre>';        print_r($e);        echo '</pre>';
        // var_dump($e);
        // dd($e);

        $ms = [];
        if (!empty($e))
            foreach ($e as $mag) {
                $m = [];

                // echo '1';

                $m['address'] = $mag->find('a.address')->plaintext;
                $m['kolvo'] = preg_replace("/[^,.0-9]/", '', $mag->find('.in-stock')->plaintext);
                //     // echo PHP_EOL . $link_page->innertext;
                //     // if (isset($link_page->innertext) && is_numeric($link_page->innertext) && $link_page->innertext > $pageLast) {
                //     //     // echo $html;
                //     //     $pageLast = $link_page->innertext;
                //     // echo PHP_EOL . trim($good->innertext);

                $ms[] = $m;
            }
        return $ms;
    }

    /**
     * загрузка страниц товаров 
     *
     * @return \Illuminate\Http\Response
     */
    public function loadingPagesPhantom()
    {

        $r = [];

        $web_driver = RemoteWebDriver::create(
            // "http://selenoid:4444/wd/hub",
            "http://localhost:4444/wd/hub",
            // "http://192.168.112.4:4444/wd/hub",
            // "http://192.168.112.1:4444/wd/hub",
            // "selenoid:4444/wd/hub",
            // "http://localhost:4444/wd/hub",
            // "http://127.0.0.1:4444/wd/hub",
            // "http://localhost:4444",
            // "http://selenoid:4444/wd/hub",
            array(
                "browserName" => "firefox",
                "browserVersion" => "106.0"
            )
        );

        $web_driver->get('https://www.google.com/');

        echo 'ok';

        dd('eee');

        //         $host = 'http://localhost:4444/wd/hub';
        //         $capabilities = DesiredCapabilities::chrome();
        //         $driver = RemoteWebDriver::create($host, $capabilities);
        //         $driver->get('https://www.google.com/');
        //         print 'ok';

        // dd('fdf');



        $timerStart = microtime(true);

        $nn = 0;

        $pages = Good::where('load-type', 'new')->limit(10)->get();
        foreach ($pages as $p) {

            $nn++;

            $r[] = $p->toArray();

            // $page = 'https://zakrepi.ru/catalog/' . $p->uri;
            // // $phantom_script = dirname(__FILE__) . '/i3.js' . ' ' . $page . ' ' . $to;
            // //         // http://localhost/storage/phantom/i3.js
            // $to = './../xxxx.htm';
            // $phantom_script = 'http://localhost/storage/phantom/i3.js' . ' ' . $page . ' ' . $to;

            // // //        // //     $phantom_script = 'i3.js' . ' ' . $page . ' ' . $to;
            // // // //         // //     echo $phantom_script;
            // // // //         // //     echo '<br/>';
            // // $response =  shell_exec('phantomjs ' . $phantom_script . ' 2>&1 &');
            // $response =  shell_exec('http://localhost:4444/wd/hub/ ' . $phantom_script . ' 2>&1 &');

            $host = 'http://localhost:4444/wd/hub'; // прослушивается Selenium Standalone Server
            // $host = 'http://0.0.0.0:4444/wd/hub'; // прослушивается Selenium Standalone Server
            // $host = 'http://localhost:4444'; // прослушивается Selenium Standalone Server
            // $desiredCapabilities = DesiredCapabilities::chrome();

            // $desiredCapabilities = DesiredCapabilities::firefox();
            // $driver = RemoteWebDriver::create($host, $desiredCapabilities);

            $driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
            $driver->get("http://php-cat.com");

            dd([__LINE__, '$p' => $p, '$response' => $response]);

            //         echo PHP_EOL . '<br/>- ' . $cat;
            // // //         // if ($nn <= 2) {
            // // //         $t = round(microtime(true) - $start, 4);

            // // echo PHP_EOL.' l '.round($t-$last,2);

        }

        dd($r);

        return $r;
    }

    /**
     * загрузка страниц товаров 
     *
     * @return \Illuminate\Http\Response
     */
    public function loadingPages()
    {

        $timerStart = microtime(true);
        $nn = 0;

        $pages = Good::where('load-type', 'new')->limit(5)->get();
        foreach ($pages as $p) {

            $nn++;

            // $pageHtml = 'data_za/cat/' . $cat->uri . '/1pageScan.htm';
            $pageToServer = '/data_za/pages/' . $p->uri . '.htm';
            $uriServer = 'https://zakrepi.ru/catalog/' . $p->uri;

            echo 'uri serv: ' . $uriServer;

            $load = LoaderController::loadPageFromInet(
                $uriServer,
                $pageToServer,
                [
                    'addToGet' => ['isAjax' => 'true',]
                ]
            );

            if (!empty($load['content'])) {

                echo '<br/> есть контент';
                $ww = self::parsingGoodsFromHtml($load['content'], $p->uri ?? '');

                $new = array_merge($p->toArray(), $ww['good']);
                $new['load-type'] = 'loaded';
                $polya = [
                    "cat-id",
                    "name",
                    "uri",
                    "img",
                    "discount",
                    "opis",
                    "price",
                    "price-old",
                    "brand",
                    "articul",
                    "kod",
                    "load-type"
                ];

                $in2 = [];

                foreach ($new as $k => $v) {
                    if (in_array($k, $polya)) {
                        // echo '<br/>' . $k . ' ++';
                        $in2[$k] = $v;
                    }
                    // else{
                    //     echo '<br/>' . $k . ' --';
                    // }
                }

                $in2['created_at'] =
                    $in2['updated_at'] = date('Y-m-d H:i:s');
                $in2['load-type'] = 'loaded';

                // echo '<pre>', print_r($ww), '</pre>';

                // $in = $ww['good'];
                // Good::where('id', $p->id)->update($in);
                // Good::where('id', $p->id)->delete();
                Good::where('uri', $p->uri)->delete();
                $idNew = Good::insertGetId($in2);

                // dd([
                //     'good-new' => $ww,
                //     'good-now' => $p->toArray(),
                //     'new' => $new,
                //     'new-in2' => $in2,
                //     '$idNew = ' => $idNew
                //     // $load['content']
                // ]);

                echo '<br/>' . '<br/>' . $uriServer;
                $timerStop = round(microtime(true) - $timerStart, 1);
                echo '<Br/>' . $timerStop . ' сек ';
                sleep(1);
            } else {
                echo '<br/> нет контент';
            }

            flush();

            // dd([ __FILE__ ,  __LINE__ , $load]);

            // if ($load['type'] == 'est' || $load['type'] == 'load') {
            //     $pages_ok = CatPageParsing::find($p->id);
            //     $pages_ok->status = 'loaded';
            //     $pages_ok->save();
            // }

            // // dd([
            // //     'end',
            // //     $load,
            // //     $p,
            // //     $pages_ok,
            // // ]);

            // echo '<br/>' . $pageToServer;

            // $timerStop = round(microtime(true) - $timerStart, 3);
            // echo $timerStop . ' сек ';

            // flush();
            // sleep(1);

            // if( $timerStop > 25 )
            // break;

        }

        if ($nn > 0)
            echo '<script> location.reload(); </script>';

        // flush();
        // dd($pages);

        // dd('end');

    }
}
