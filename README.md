<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel 9 + DomCrawler

## Consumir dados da fenabrave

- http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Sao%20Paulo&cap=
- http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Goias&cap=
- http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Distrito%20Federal&cap=
- http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Minas%20Gerais&cap=

## DatabaseSeeder
`php artisan db:seed `
> storage/app/UF_GO.csv

> Este código usa a biblioteca Symfony DomCrawler para fazer o parsing do HTML, e a biblioteca Http do Laravel para fazer a requisição HTTP. Em seguida, ele extrai os dados da tabela, formata-os como uma string CSV e salva o conteúdo no arquivo "UF_GO.csv" usando o Laravel Storage.

- Renato Lucena
