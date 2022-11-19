<?php

namespace App\Http\Controllers;

use App\Models\Cat;
use App\Models\CatPageParsing;
use App\Models\Good;
use Illuminate\Http\Request;

class ParserController extends Controller
{

    /**
     * какой аг следующий
     * что делать для парсинга
     */
    public function howNextStep()
    {
        $r = [
            'now_step' => '',
            'variants' => [
                // если нет каталогов, то сканим список каталогов
                'scan_list_cat',
                // если нет количества страниц в главных каталогах
                'scan_kolvo_pages',
                // если нет страниц для парсинга из главных каталогов
                // то записываем страницы
                'record_kolvo_page_for_scan',
                // если есть непропарсенные страницы каталогов то сканим
                'parsing_cats_page',
                // если есть непросканенные первый раз товары, сканим
                'parsing_goods_new',
                // если есть старопарсенные товары ... сканим их заново
                'parsing_old_goods',
                // если всё хорошо то вери гут
                'very_good',
            ]
        ];

        // проваерка на каталоги
        $s = self::scanCatalogs();
        if (empty($s['all'])) {
            $r['now'] = 'scan_list_cat';
        }
        // кол-во страниц каталогов для парсинга
        elseif ($s['no_pages'] !== 0) {
            $r['now'] = 'scan_kolvo_pages';
        } else {

            $e = self::scanCatalogsScanPages();

            if ($e['new'] > 0) {
                $r['now'] = 'parsing_cats_page';
            } else {
                $r['now'] = 'very_good';
            }
        }

        return $r;
        // return response()->json($r);

    }


    /**
     * тукенция запускаемая 500 раз в минуту
     *
     * @return \Illuminate\Http\Response
     */
    public function go()
    {
        $n = self::howNextStep();

        if ($n['now'] == 'scan_list_cat') {
            $n['result'] = CatController::get();
        } elseif ($n['now'] == 'parsing_cats_page') {
            $n['result'] = CatController::loadingParsingCatPages();
        } elseif ($n['now'] == 'scan_kolvo_pages') {
            $n['result'] = CatController::get1page();
        }

        dd($n);

        return $n;
    }


    /**
     * каталоги просканены ?
     *
     * @return \Illuminate\Http\Response
     */
    public function scanCatalogs()
    {
        $r = [];
        $r['all'] = Cat::all()->count();
        $r['no_pages'] = Cat::whereNull('cat_up_id')->whereNull('pages')->count();
        return $r;
    }

    public function scanCatalogsScanPages()
    {
        $r = [];
        $r['all'] = CatPageParsing::all()->count();
        $r['new'] = CatPageParsing::where('status', 'new')->count();
        $r['loaded'] = CatPageParsing::where('status', 'loaded')->count();
        $r['parsing_ok'] = CatPageParsing::where('status', 'parsing_ok')->count();
        return $r;
    }


    public function scanGoods()
    {
        $r = [];
        $r['all'] = Good::all()->count();
        $r['new'] = Good::where('load-type', 'new')->count();
        $r['loaded'] = Good::where('load-type', 'loaded')->count();
        $r['full'] = Good::where('load-type', 'full')->count();
        // $r['scan_pages_new'] = CatPageParsing::where('status', 'new')->count();
        // $r['scan_pages_loaded'] = CatPageParsing::where('status', 'loaded')->count();
        // $r['scan_pages_parsing_ok'] = CatPageParsing::where('status', 'parsing_ok')->count();
        return $r;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $e = self::scanCatalogs();
        echo 'self::scanCatalogs();';
        echo '<pre>', print_r($e), '</pre>';

        echo 'self::scanCatalogsScanPages();';
        $e = self::scanCatalogsScanPages();
        echo '<pre>', print_r($e), '</pre>';
        echo 'self::scanGoods();';
        $e = self::scanGoods();
        echo '<pre>', print_r($e), '</pre>';
        dd(__FILE__, __LINE__, __FUNCTION__);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        dd(__FILE__, __LINE__, __FUNCTION__);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}