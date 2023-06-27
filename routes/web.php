<?php

use Illuminate\Support\Facades\Route;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/extrair-tabela', function () {
    $url = 'http://www.fenabrave.org.br/portalv2/Conteudo/dadosregionais';

    $client = new Client();
    $crawler = $client->request('GET', $url);
    dd($crawler);
    $tabela = $crawler->filter('#conteudoNoticia table')->html();

    return $tabela;
});
