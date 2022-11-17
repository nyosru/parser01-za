<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Page;

class LoaderController extends Controller
{

    /**
     * грузим страницу и сохраняем для кеша7
     *
     * @return \Illuminate\Http\Response
     */
    public function loadPageFromInet(string $uri, string $saveToFile, array $dops = [])
    {
        $r = [
            'uri' => $uri,
            // 'to_file' => $saveToFile,
            'dop' => $dops
        ];
        // dd($r);
        $uriInBd = $uri;
        if (!empty($dops)) {
            if (!empty($dops['addToGet'])) {
                $uriInBd2 = '';
                foreach ($dops['addToGet'] as $k => $v) {
                    $uriInBd2 .= (!empty($uriInBd2) ? '&' : '') . $k . '=' . $v;
                }
                $uriInBd .= '?' . $uriInBd2;
            }
        }

        $PageDB = Page::where('uri', $uriInBd)->limit(1)->get();
        $r['to_file'] = $saveToFile;

        // если в бд есть страница, тащим от туда
        if ($PageDB->count() == 1) {
            
            $r['content'] = $PageDB[0]->html;
            $r['type'] = 'estInDb';

        } else {

            if (Storage::exists($saveToFile)) {
                $r['content'] = Storage::get($saveToFile);
                $r['type'] = 'est';
            } else {


                $r['type'] = 'load';
                    // $type = 'loadng file';
                    // Http::dd()->get('http://example.com/users', [

                    // $page = $uri
                    // . '?&isAjax=true'
                ;
                // $ee['pageLoadHtml'] = $page;

                // $jar = new \GuzzleHttp\Cookie\CookieJar();
                // $client->request('GET', '/get', ['cookies' => $jar]);

                $rr = Http::get(
                    $uri,
                    $dops['addToGet'] ?? []
                );
                $r['content'] = $rr->body();
                // Storage::put($saveToFile, $r['content']);
                
            }

            Page::insert([
                [
                    'uri' => $uriInBd,
                    'html' => $r['content']
                ]
            ]);

        }

        // $ee['$type'] = $type;
        return $r;
    }
}
