<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;
use App\Models\Cat;
use App\Models\CatPageParsing;
use App\Models\Good;
use App\Http\Controllers\LoaderController;

use Symfony\Component\DomCrawler\Crawler;

class CatController extends Controller
{

    /**
     * использую в парсинге каталогов чтобы обозначить кат сверху
     */
    static $last = '';

    /**
     * парсинг каталогов из файла данных
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {

        $catCount = Cat::all()->count();
        // dd($catAll);

        if (!empty($catCount))
            dd('каталоги уже есть');

        // if ($_REQUEST['step'] == 'cat') {
        $page = 'https://zakrepi.ru/catalog';
        $to = 'f1/za-catalog.htm';

        if (Storage::exists('data_za/catAll.htm')) {
            $content = Storage::get('data_za/catAll.htm');
            $type = 'est';
        } else {
            $type = 'loadng file';
            // Http::dd()->get('http://example.com/users', [
            $rr = Http::get(
                $page
                // , [
                //     'name' => 'Taylor',
                //     'page' => 1,
                // ]
            );
            $content = $rr->body();
            Storage::put('data_za/catAll.htm', $rr->body());
        }

        $cats = self::parserCatsFromHtmlFile('data_za/catAll.htm');

        $catsDb = [];
        foreach ($cats as $uri => $name) {
            $catsDb[] = Cat::create([
                'uri' => $uri,
                'name' => $name
            ]);
        }

        $catAll = Cat::all();

        dd([
            'type' => $type,
            '$catAll' => $catAll,
            'cats' => $cats,
            '$catsDb' => $catsDb,
            'r' => $content, 11 => 22
        ]);
    }

    /**
     * парсинг списка каталогов из html файла
     *
     * @return \Illuminate\Http\Response
     */
    public function parserCatsFromHtmlFile($file)
    {

        // echo '<br/>';
        // echo '<br/>';
        // echo '<br/>';
        // echo __FILE__ . ' ' . __LINE__;

        // echo '<pre>';
        // print_r(scandir( $_SERVER['DOCUMENT_ROOT'] ));
        // echo '</pre>';

        // echo '<pre>';
        // print_r(scandir( __DIR__.'/../'));
        // echo '</pre>';

        if (!function_exists('str_get_html'))
            include($_SERVER['DOCUMENT_ROOT'] . '/resources/php/simplehtmldom_1_9_1/simple_html_dom.php');

        $content = Storage::get($file);

        // echo PHP_EOL . 'смо  трим cat: ' . $cat;
        $html = str_get_html($content);
        // $pageLast = 0;

        $in = [];

        foreach ($html->find('a') as $aa) {
            // /categories/
            // echo PHP_EOL . $aa->href . ' ' . $aa->innertext;
            // if ($aa->href == '/categories/krepyzh')
            //     continue;

            if (strpos($aa->href, '/categories/') !== false) {

                if (trim($aa->innertext) == 'Перейти в раздел')
                    continue;

                $cat2 = str_replace('/categories/', '', $aa->href);
                // echo PHP_EOL . $cat2 . ' ' .
                // $aa->href . ' ' .
                if (!isset($in[$cat2]))
                    $in[$cat2] = trim($aa->innertext);
            }

            // // echo PHP_EOL . $link_page->innertext;
            // if (isset($link_page->innertext) && is_numeric($link_page->innertext) && $link_page->innertext > $pageLast) {
            //     // echo $html;
            //     $pageLast = $link_page->innertext;
            // }


        }

        // dd($in);
        return $in;
    }


    /**
     * загрузка страниц каталогов для парсинга
     *
     * @return \Illuminate\Http\Response
     */
    public function loadingPages()
    {

        $timerStart = microtime(true);

        $pages = CatPageParsing::where('status', 'new')->limit(50)->get();
        foreach ($pages as $p) {

            // $pageHtml = 'data_za/cat/' . $cat->uri . '/1pageScan.htm';
            $pageToServer = '/data_za/cat/' . $p->cat_uri . '/' . $p->page . ($p->page == 1 ? 'pageScan' : '') . '.htm';

            $load = LoaderController::loadPageFromInet(
                'https://zakrepi.ru/categories/' . $p->cat_uri,
                $pageToServer,
                [
                    'addToGet' => ['isAjax' => 'true',]
                ]
            );

            if ($load['type'] == 'est' || $load['type'] == 'load') {
                $pages_ok = CatPageParsing::find($p->id);
                $pages_ok->status = 'loaded';
                $pages_ok->save();
            }

            // dd([
            //     'end',
            //     $load,
            //     $p,
            //     $pages_ok,
            // ]);

            echo '<br/>' . $pageToServer;

            $timerStop = round(microtime(true) - $timerStart, 3);
            echo $timerStop . ' сек ';

            flush();
            sleep(1);

            if ($timerStop > 25)
                break;
        }
        // dd($pages);

        dd('end');
    }

    /**
     * парсим первую страницу каждого каталога (смотрим сколько страниц и вложенность в каталоги)
     *
     * @return \Illuminate\Http\Response
     */
    public function get1page()
    {

        $ee = [];

        $nowCat0 = Cat::whereNull('pages');
        $nowCatColvo = $nowCat0->count();
        echo '$nowCatColvo:' . $nowCatColvo . ' ( ' . round(($nowCatColvo * 3) / 60, 1) . ' мин ) ';
        $nowCat = Cat::whereNull('pages')->limit(500)->get();
        $timerStart = microtime(true);

        // dd($nowCat);
        foreach ($nowCat as $cat) {

            $pageHtml = 'data_za/cat/' . $cat->uri . '/1pageScan.htm';

            $ee['pageMyHtml'] = $pageHtml;
            // $page = 'https://zakrepi.ru/categories/' . $cat->uri

            $load = LoaderController::loadPageFromInet('https://zakrepi.ru/categories/' . $cat->uri, $pageHtml, [
                'addToGet' => ['isAjax' => 'true',]
            ]);
            $ee['type'] = $load['type'];
            // $ee['html'] = $load['content'];

            // dd($load);

            if (!function_exists('str_get_html'))
                include($_SERVER['DOCUMENT_ROOT'] . '/resources/php/simplehtmldom_1_9_1/simple_html_dom.php');

            // $html = str_get_html($content);
            $html = str_get_html($load['content']);

            $maxPage = self::getMaxPageGoodsOnPageCat($html);
            $ee['maxPage'] = $maxPage > 0 ? $maxPage : 1;
            self::setMaxPage($cat, $ee['maxPage']);

            $ee['cats'] = self::getOnCatPage_CatsList($html);
            $ee['cats_save'] = self::saveCats($ee['cats']['cats'], $ee['cats']['cat-sub']);

            $ee['goodsOnPage'] = self::getOnCatPage_Goods($html, $ee['cats_save']['nowCat']);
            $ee['goodsAdd'] = self::saveGoods($ee['goodsOnPage']);
            // $ee['html'] = $content;

            echo '<br/>pageMyHtml: ' . $ee['pageMyHtml'];
            $timerStop = round(microtime(true) - $timerStart, 3);
            echo $timerStop . ' сек ';

            flush();

            if ($timerStop > 15)
                break;

            // dd($ee);
        }

        // }
        // dd($in);
        // return $in;
    }

    /**
     * вытягиваем какая таблица максимальная на странице каталога
     *
     * @return \Illuminate\Http\Response
     */
    public function setMaxPage(Cat $cat, $pageColvo = 1)
    {

        // if (1 == 2)
        if (empty($cat->pages)) {
            $cat->pages = $pageColvo;
            $cat->save();
            // dd([
            //     'line' => __LINE__,
            //     'cat_name' => $cat->name,
            //     'cat' => $cat
            // ]);
        }

        return true;
    }

    /**
     * getOnCatPage_CatsList
     *
     * @return \Illuminate\Http\Response
     */
    public function getOnCatPage_CatsList($html)
    {

        $r = [
            'cats' => [],
            'cat-sub' => [],
            'last_cat' => null
        ];

        $inf['cats'] = [];
        $cat2 = [];
        $last_cat = '';
        $list_cats = [];

        // $vop = $html->find('#description div[itemprop=description]', 0);
        foreach ($html->find('.breadcrumbs a') as $link) {
            // $vop = $html->find('#description div', 0);
            if ($link) {
                $li = str_replace('/categories/', '', $link->href);
                if (strpos($li, 'https') === false) {
                    $list_cats[] = $li;
                    $r['cats'][] = [
                        'link' => $li,
                        'cat_up' => $last_cat,
                        'name' => $link->find('[itemprop=name]', 0)->innertext
                    ];
                    $r['last_cat'] =
                        $last_cat = $li;
                }
            }
        }

        foreach ($html->find('.sub-categories a') as $link) {
            if ($link) {
                $li = str_replace('/categories/', '', $link->href);
                if (strpos($li, 'https') === false) {

                    $r['cat-sub'][] = [
                        'link' => $li,
                        'cat_up' => $last_cat,
                        'name' => trim($link->innertext)
                    ];
                    // $last_cat = $li;
                }
            }
        }

        return $r;
    }


    /**
     * тащим из страницы товара каталоги вложенность
     */
    public function parsingGoodCats($crawler)
    {
        $re = [];

        //     <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
        //     <a itemprop="item" href="https://zakrepi.ru/catalog">
        //         <span itemprop="name">Каталог</span>
        //     </a>
        //     <meta itemprop="position" content="2"/>
        // </li>

        try {

            self::$last = '';

            $re = $crawler->filter('li[itemprop=itemListElement]')->each(function (Crawler $node, $i) {

                $link = $node->filter('a')->attr('href');

                if (strpos($link, 'categories') !== false) {
                    $a = $b = [];
                    $a[] = '/categories/';
                    $b[] = '';

                    $re = [
                        'name' => $node->filter('span')->text(),
                        'link' => str_replace($a, $b, $link),
                    ];

                    if (!empty(self::$last))
                        $re['cat_up'] = self::$last;

                    self::$last = $re['link'];

                    return $re;
                    // return $node->filter('div')image()->getUri();
                }
            });
        } catch (\Throwable $th) {
        }

        // dd(__FILE__, __LINE__, $re);

        return $re;
    }


    /**
     * saveCats($catSteps = [], $catSubs = [] )
     * catSteps - каталоги один за одним по вложенности
     * $catSubs - каталоги вложенные в последний в степах
     */
    public function saveCats($catSteps = [], $catSubs = [])
    {

        $return = [
            'linkOnStart' => [],
            'linknDb' => [],
            'arrayToInsert' => [],
            'nowCat' => 0
        ];

        // dd([__FUNCTION__, $catSteps, $catSubs]);
        // $cc = array_merge($catSteps, $catSubs);
        if (!empty($catSteps))
            foreach ($catSteps as $c) {

                if (empty($c))
                    continue;

                if (!isset($return['linkOnStart'][$c['link']])) {
                    $return['linkOnStart'][] = $c['link'];
                    $return['linkOnStart2'][$c['link']] = $c;
                    $return['nowCat'] = $c['link'];
                }
            }

        if (!empty($catSubs))
            foreach ($catSubs as $c) {
                if (!isset($return['linkOnStart'][$c['link']])) {
                    $return['linkOnStart'][] = $c['link'];
                    $return['linkOnStart2'][$c['link']] = $c;
                }
            }

        $catsnDb0 = Cat::whereIn('uri', $return['linkOnStart'])->get();
        $catsnDb = [];
        foreach ($catsnDb0 as $w) {
            // $ca = $w->toArray();
            // $catsnDb[$w->uri] = $ca;
            $return['linkInDb'][$w->uri] = $w->toArray();
        }

        $return['nowCat'] = $return['linkInDb'][$return['nowCat']]['id'] ?? '';

        foreach ($return['linkOnStart2'] as $k => $v) {
            $return['arrayToInsert'][] = [
                'uri' => $v['link'],
                'name' => $v['name'],
                'cat_up_id' => ( isset($v['cat_up']) ? $return['linkInDb'][$v['cat_up']]['id'] ?? NULL : NULL )
            ];
        }

        // dd($return);

        $return['res'] = Cat::upsert(
            $return['arrayToInsert'],
            ['uri'],
            ['name', 'cat_up_id']
        );

        // dd($return);
        // if (1 == 2)
        //     if (empty($cat->pages)) {
        //         $cat->pages = $pageColvo;
        //         $cat->save();
        //         // dd([
        //         //     'line' => __LINE__,
        //         //     'cat_name' => $cat->name,
        //         //     'cat' => $cat
        //         // ]);
        //     }

        return $return;
    }
    public function saveGoods($goods = [])
    {

        // dd($goods);
        // foreach( // dd($goods);            
        $return['res0'] = Good::whereIn('uri', array_column($goods, 'uri'))
            ->delete();
        $return['res'] = Good::insert($goods);

        // dd($return);
        return $return;

        // $return = [
        //     'linkOnStart' => [],
        //     'linknDb' => [],
        //     'arrayToInsert' => [],
        //     'nowCat' => 0
        // ];

        // // dd([__FUNCTION__, $catSteps, $catSubs]);
        // // $cc = array_merge($catSteps, $catSubs);
        // foreach ($catSteps as $c) {
        //     if (!isset($return['linkOnStart'][$c['link']])) {
        //         $return['linkOnStart'][] = $c['link'];
        //         $return['linkOnStart2'][$c['link']] = $c;
        //         $return['nowCat'] = $c['link'];
        //     }
        // }

        // foreach ($catSubs as $c) {
        //     if (!isset($return['linkOnStart'][$c['link']])) {
        //         $return['linkOnStart'][] = $c['link'];
        //         $return['linkOnStart2'][$c['link']] = $c;
        //     }
        // }

        // $catsnDb0 = Cat::whereIn('uri', $return['linkOnStart'])->get();
        // $catsnDb = [];
        // foreach ($catsnDb0 as $w) {
        //     // $ca = $w->toArray();
        //     // $catsnDb[$w->uri] = $ca;
        //     $return['linkInDb'][$w->uri] = $w->toArray();
        // }

        // $return['nowCat'] = $return['linkInDb'][$return['nowCat']]['id'] ?? '';

        // foreach ($return['linkOnStart2'] as $k => $v) {
        //     $return['arrayToInsert'][] = [
        //         'uri' => $v['link'],
        //         'name' => $v['name'],
        //         'cat_up_id' => $return['linkInDb'][$v['cat_up']]['id'] ?? NULL
        //     ];
        // }

        // // dd($return);

        // $return['res'] = Cat::upsert(
        //     $return['arrayToInsert'],
        //     ['uri'],
        //     ['name', 'cat_up_id']
        // );

        // // dd($return);
        // // if (1 == 2)
        // //     if (empty($cat->pages)) {
        // //         $cat->pages = $pageColvo;
        // //         $cat->save();
        // //         // dd([
        // //         //     'line' => __LINE__,
        // //         //     'cat_name' => $cat->name,
        // //         //     'cat' => $cat
        // //         // ]);
        // //     }

        // return $return;
    }

    public function creatListScanPage()
    {
        $cats = Cat::select(['uri', 'pages'])->orderBy('pages', 'DESC')->limit(20)->get();

        $in = [];

        foreach ($cats as $v) {
            for ($i = 1; $i <= $v->pages; $i++) {
                $in[] = [
                    'cat_uri' => $v->uri,
                    'page' => $i,

                ];
            }
        }

        // dd([$in]);

        $ww = CatPageParsing::insert($in);
        // $ww = CatPageParsing::all();
        // dd(['creatListScanPage', $ee->toArray()]);

        dd($in);
    }

    public function showList()
    {

        $ee = Cat::all();
        $a = self::getListInner($ee);
        // dd($a);
        // dd($ee);
        return view('catList', ['cats' => $a]);
    }

    public function getListInner($cats, $up_id = null)
    {
        $return = [];
        foreach ($cats as $k) {
            if ($k->cat_up_id == $up_id) {
                $return[$k->id] = $k->toArray();
            }
        }

        foreach ($return as $k0 => $k) {
            $return[$k['id']]['in'] = self::getListInner($cats, $k['id']);
        }

        return $return;
    }



    public function getOnCatPage_Goods($html, $cat_id = null)
    {

        $goods_list = [];
        foreach ($html->find('.product-in-list') as $good) {
            // echo PHP_EOL . $link_page->innertext;
            // if (isset($link_page->innertext) && is_numeric($link_page->innertext) && $link_page->innertext > $pageLast) {
            //     // echo $html;
            //     $pageLast = $link_page->innertext;
            // echo PHP_EOL . trim($good->innertext);
            // echo PHP_EOL;
            // echo PHP_EOL;
            // }
            try {

                //code...

                if ($good->find('div.title a')[0]) {

                    $go = [
                        // 'dt' => time()
                        // 'd' => date('Y-m-d'),
                        // 't' => date('H:i:s'),
                        'img' => null,
                        'price' => null,
                        'price-old' => null,
                        'discount' => null,
                        'cat-id' => $cat_id
                    ];

                    $go['name'] = trim($good->find('div.title a')[0]->plaintext);
                    $go['uri'] = str_replace('/catalog/', '', trim($good->find('div.title a', 0)->href));
                    $go['price'] = preg_replace("/[^,.0-9]/", '', $good->find('.price')[0]->plaintext);

                    $vop = $good->find('.old-price', 0);
                    if ($vop)
                        $go['price-old'] = preg_replace("/[^,.0-9]/", '', $vop->plaintext);

                    // $go['priceOld'] = trim($good->find('.old-price')[0]->plaintext);

                    $vop = $good->find('.discount', 0);
                    if ($vop) {
                        $go['discount'] = trim($vop->plaintext);
                        if ($go['discount'] == 'Промокод') {
                            $i1 = $i2 = [];
                            $i1[] = '&lt;strong&gt;';
                            $i2[] = '';
                            $i1[] = '&lt;/strong&gt;';
                            $i2[] = '';
                            $go['discount'] = str_replace($i1, $i2, trim($vop->title));
                        }
                    }

                    $vop = $good->find('.image img', 0);
                    if ($vop) {
                        // if (strpos($go['img'], 'screw.svg') !== false) {
                        if (strpos($vop->src, 'screw.svg') !== false) {
                        } elseif (!empty($vop->src)) {
                            $go['img'] = trim($vop->src);
                            // $go['img'] = null;
                        }
                    }

                    $goods_list[] = $go;
                } else {
                    echo PHP_EOL . ' нет товара, пропускаем #' . __LINE__;
                }
            } catch (\Throwable $th) {
                //throw $th;
                echo PHP_EOL . ' нет товара, пропускаем #' . __LINE__;
            }

            // echo '<pre>', print_r($go), '</pre>';
            // echo PHP_EOL;
            // echo PHP_EOL;
            // echo PHP_EOL;

        }

        // dd($goods_list);

        return $goods_list;
    }

    /**
     * вытягиваем какая таблица максимальная на странице каталога
     *
     * @return \Illuminate\Http\Response
     */
    public function getMaxPageGoodsOnPageCat($html)
    {

        $pageLast = 0;

        foreach ($html->find('a.page-link') as $link_page) {
            if (isset($link_page->innertext) && is_numeric($link_page->innertext) && $link_page->innertext > $pageLast) {
                $pageLast = $link_page->innertext;
                // echo '#'.__LINE__.' '.$pageLast;
            }
        }

        return $pageLast;
    }

    /**
     * тащим из страницы каталога что за каталоги и какой в какой вложен
     *
     * @return \Illuminate\Http\Response
     */
    public function scanCatPageOnCats($html)
    {
        // return $in;
    }
}
