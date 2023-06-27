<?php

namespace Database\Seeders;

use Carbon\Carbon;
use HungCP\PhpSimpleHtmlDom\HtmlDomParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// |TESTE
class FenabraveImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1G');
        ini_set('default_socket_timeout', 300);
        ini_set("user_agent", "user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36");
        $atualSalvo = false;

        $banco = DB::connection('fenabrave');

        $ultimoSinistro = $banco->table('fenabrave')->orderBy('proo_id', 'DESC')->first();
        $prooId = $ultimoSinistro->proo_id + 1;

        $qtdeTentativas = 0;
        $loop = true;

        while ($loop) {
            $opts = array('http' => array(
                'header' => array('User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36', 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'),
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                'timeout' => 300,
            ));
            $context = stream_context_create($opts);

            $url = 'http://www.fenabrave.org.br/pdf/informativo/automatico/dadosregionais_novo.asp?id=Goias&cap=';

            $html = HtmlDomParser::file_get_html($url, false, $context, 0);
            $data = $this->listarColunas($html);
            $registro = [];
            $valido = false;
            $dataAgora = Carbon::now();
            Log::debug('importar: ' . json_encode($data));
            foreach ($data as $key => $linha) {
                if ($key == 0) {
                    $data = explode(': ', $linha[1]);

                    if (isset($data[1]) && $data[1]) {
                        $valido = true;
                        $registro['data'] = $data[1];
                    }
                } elseif ($valido) {
                    $registro = $this->organizarRegistro($registro, $linha, $key);
                } else {
                    break;
                }
            }

            if ($valido) {
                $registro = collect($registro);

                $registro = $registro->map(function ($dado) {
                    $dado = $this->mudarCaracteres($dado);
                    $dado = trim($dado);

                    return $dado;
                });

                $body = $html->find('body', 0);
                $registro['site_html'] = minifyHtml($body->innertext);
                $registro['proo_id'] = $prooId;
                $registro['empresa_id'] = null;
                $registro['created_at'] = $dataAgora;
                $registro['updated_at'] = $dataAgora;

                $banco->table('fenabrave')->insert($registro->toArray());
            }

            $atualSalvo = $valido;
            $prooId++;

            if (!$atualSalvo) {
                $qtdeTentativas++;

                if ($qtdeTentativas > 100) {
                    $loop = false;
                }
            }
        }

        // postgresql //$banco->statement("SELECT setval(pg_get_serial_sequence('fenabrave', 'id'), coalesce((max(id)+1),1), false) FROM fenabrave;");
    }

    /**
     * Muda os caracteres especiais de uma string
     *
     * @param string $str
     * @return string $str
     */
    public function mudarCaracteres($str)
    {
        $str = str_replace('&nbsp;', '', $str);

        return $str;
    }

    /**
     * Lista as colunas da tabela da página
     *
     * @param \simplehtmldom_1_5\simple_html_dom $html
     * @return array $colunas
     */
    public function listarColunas($html)
    {
        $colunas = [];

        foreach ($html->find('table') as $key => $table) {
            if ($key != 1) {
                foreach ($table->find('tr') as $keyTr => $tr) {
                    if ($key != 2 || $keyTr > 0) {
                        $row = [];

                        foreach ($tr->find("td") as $td) {
                            $row[] = $td->plaintext;
                        }

                        $colunas[] = $row;
                    }
                }
            }
        }

        return $colunas;
    }

    /**
     * Organiza o resultado em um array
     *
     * @param array $registro
     * @param array $linha
     * @param int $key
     * @return array $registro
     */
    public function organizarRegistro($registro, $linha, $key)
    {
        if ($key == 1) {
            $registro['sin_seguradora'] = $linha[1];
            $registro['sin_wagner'] = $linha[3];
            $registro['sin_ramo'] = $linha[5];
        } elseif ($key == 2) {
            $registro['seguradora'] = $linha[1];
        } elseif ($key == 3) {
            $registro['segurado'] = $linha[1];
            $registro['segurado_cnpj'] = $linha[3];
        } elseif ($key == 4) {
            $registro['segurado_contato'] = $linha[1];
            $registro['segurado_fone'] = $linha[3];
        } elseif ($key == 5) {
            $registro['corretor'] = $linha[1];
        } elseif ($key == 6) {
            $registro['corretor_contato'] = $linha[1];
            $registro['corretor_fone'] = $linha[3];
        } elseif ($key == 7) {
            $registro['transportador'] = $linha[1];
            $registro['transportador_fone'] = $linha[3];
        } elseif ($key == 8) {
            $registro['motorista'] = $linha[1];
            $registro['cnh'] = $linha[3];
        } elseif ($key == 9) {
            $registro['cpf'] = $linha[1];
            $registro['rg'] = $linha[3];
        } elseif ($key == 10) {
            $registro['motorista_vinculo'] = $linha[1];
        } elseif ($key == 11) {
            $registro['veiculo'] = $linha[1];
            $registro['placa'] = $linha[3];
        } elseif ($key == 12) {
            $registro['remetente'] = $linha[1];
            $registro['origem'] = $linha[3];
        } elseif ($key == 13) {
            $registro['destinatario'] = $linha[1];
            $registro['destino'] = $linha[3];
        } elseif ($key == 14) {
            $registro['evento_local'] = $linha[1];
            $registro['evento_cidade'] = $linha[3];
        } elseif ($key == 15) {
            $registro['causa_evento'] = $linha[1];
            $registro['evento_data'] = $linha[3];
        } elseif ($key == 16) {
            $registro['manifesto_numero'] = $linha[1];
            $registro['manifesto_data_emissao'] = $linha[3];
        } elseif ($key == 17) {
            $registro['conhecimento_numero'] = $linha[1];
            $registro['conhecimento_data_emissao'] = $linha[3];
        } elseif ($key == 18) {
            $registro['nota_fiscal_numero'] = $linha[1];
            $registro['nota_fiscal_data_emissao'] = $linha[3];
        } elseif ($key == 19) {
            $registro['mercadoria'] = $linha[1];
            $registro['valor_declarar'] = $linha[3];
        } elseif ($key == 20) {
            $registro['carga_dano'] = $linha[1];
        } elseif ($key == 21) {
            $registro['salvados'] = $linha[1];
            $registro['ressarcimento'] = $linha[3];
            $registro['estimativa_prejuizo'] = $linha[5];
        } elseif ($key == 22) {
            $registro['destino_mercadoria_sinistro'] = $linha[1];
        } elseif ($key == 23) {
            $registro['previsao_chegada'] = $linha[1];
        } elseif ($key == 24) {
            $registro['delegacia_policia'] = $linha[1];
            $registro['boletim_ocorrencia'] = $linha[3];
        } elseif ($key == 25) {
            $registro['gerenciadora_riscos'] = $linha[1];
            $registro['veiculo_localizado'] = $linha[3];
        } elseif ($key == 26) {
            $registro['tipo_equipamento'] = $linha[1];
            $registro['veiculo_rastreado'] = $linha[3];
        } elseif ($key == 27) {
            $registro['empresa_sindicante'] = $linha[1];
            $registro['numero_liberacao'] = $linha[3];
        } elseif ($key == 28) {
            $registro['analista_seguradora'] = $linha[1];
            $registro['atendente_wagner'] = $linha[3];
        } elseif ($key == 29) {
            $registro['comunicado_por'] = $linha[1];
            $registro['comunicado_data'] = $linha[3];
            $registro['comunicado_hora'] = $linha[5];
        } elseif ($key == 30) {
            $registro['descricao_evento'] = $linha[1];
        } elseif ($key == 31) {
            $registro['classificacao_produto_perigoso'] = $linha[1];
        } elseif ($key == 32) {
            $registro['gerenciadora_ambiental'] = $linha[1];
        } elseif ($key == 33) {
            $registro['observacao'] = $linha[1];
        }

        return $registro;
    }
}
