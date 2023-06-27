<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        echo 'teste';
        $this->teste();
    }

    public function teste()
    {
        $url = "http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Goias&cap=";

        $response = Http::get($url);
        $html = $response->body();

        $crawler = new Crawler($html);
        $table = $crawler->filter('table.TABELA')->first();

        $rows = $table->filter('tr')->each(function ($row) {
            return $row->filter('td')->each(function ($cell) {
                return $cell->text();
            });
        });

        $fileContent = '';
        foreach ($rows as $row) {
            $fileContent .= implode(',', $row) . "\n";
        }

        Storage::put('UF_GO.csv', $fileContent);
    }
}
