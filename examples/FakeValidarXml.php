<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Rps;
use NFePHP\NFSeEGoverne\Common\Soap\SoapFake;
use NFePHP\NFSeEGoverne\Common\FakePretty;

try {

    $config = [
        'cnpj'  => '99999999000191',
        'im'    => '1733160024',
        'cmun'  => '4106902',
        'razao' => 'Empresa Test Ltda',
        'tpamb' => 2
    ];

    $configJson = json_encode($config);

    $content = file_get_contents('expired_certificate.pfx');
    $password = 'associacao';
    $cert = Certificate::readPfx($content, $password);

    $soap = new SoapFake();
    $soap->disableCertValidation(true);

    $tools = new Tools($configJson, $cert);
    $tools->loadSoapClass($soap);
    
    $arps = [];

    $std = new \stdClass();
    $std->version = '1.00';
    $std->identificacaorps = new \stdClass();
    $std->identificacaorps->numero = '27548'; // Obrigatorio limite 15 digitos
    $std->identificacaorps->serie = 'RP'; // Obrigatorio 
    $std->identificacaorps->tipo = 1; // Obrigatorio 1 - RPS 2-Nota Fiscal Conjugada (Mista) 3-Cupom
    $std->dataemissao = '2019-03-27T11:29:35';  //Obrigatorio 
    $std->naturezaoperacao = 1;   //Obrigatorio 
// 1 – Tributação no município
// 2 - Tributação fora do município
// 3 - Isenção
// 4 - Imune
// 5 – Exigibilidade suspensa por decisão judicial
// 6 – Exigibilidade suspensa por procedimento administrativo

    $std->regimeespecialtributacao = 2; //Opcional
// 1 – Microempresa municipal
// 2 - Estimativa
// 3 – Sociedade de profissionais
// 4 – Cooperativa
// 5 – MEI – Simples Nacional
// 6 – ME EPP – Simples Nacional

    $std->optantesimplesnacional = 2;
//1 - SIM 2 - Não
    $std->incentivadorcultural = 1;
//1 - SIM 2 - Não
    $std->status = 1;
// 1 – Normal  2 – Cancelado

    $std->rpssubstituido = new \stdClass(); //Opcional
    $std->rpssubstituido->numero = '11'; //Obrigatorio se declarado RpsSubstituto
    $std->rpssubstituido->serie = '1'; //Obrigatorio se declarado RpsSubstituto
    $std->rpssubstituido->tipo = 1; //Obrigatorio se declarado RpsSubstituto

    $std->tomador = new \stdClass(); //Opcional
//$std->tomador->cnpj = "99999999000191"; //Opcional se declarado CPF
    $std->tomador->cpf = "16166869878";  //Opcional se declarado CNPJ
    $std->tomador->razaosocial = "ROSEMIR DO ROCIO FERREIRA VOSS"; //Opcional

    $std->tomador->endereco = new \stdClass(); //Opcional
    $std->tomador->endereco->endereco = 'RUA JOAQUIM COSTA RIBEIRO';  //Opcional
    $std->tomador->endereco->numero = '683';  //Opcional
    $std->tomador->endereco->complemento = 'SEM COMP';  //Opcional
    $std->tomador->endereco->bairro = 'BAIRRO ALTO';  //Opcional
    $std->tomador->endereco->codigomunicipio = '4106902';  //Opcional
    $std->tomador->endereco->uf = 'PR';  //Opcional
    $std->tomador->endereco->cep = '82840190'; //Opcional

    $std->servico = new \stdClass();
    $std->servico->itemlistaservico = '1406';
    $std->servico->codigotributacaomunicipio = '522310000';
    $std->servico->codigocnae = '4520001';
    $std->servico->discriminacao = 'REM E INST VIDRO R$ 190.33\r\r\nA FATURA AGRUPA OS VALORES DAS NF:39548/055 E 27548/RP\r\nTOTAL DA FATURA 27063 R$ 4.045,85 - COM A(S) PARCELA(S):\r\nPARC: 27063/0101 - VENC: 26/04/19 - VALOR: 4.045,85\r\r\nTRIB APROX R$: 17,61 FED / 9,52 MUN /\r\nDESCONTO SERVICOS     19,67-\r\nO.S./TIPO: 055428 / GM KM:  8803 FAB: 2018\r\nCHASSI: LYVUZ10CCKB214051 PLACA: BEN0995\r\nMOD/VER: 246       - XC60 2.0 DR\r\nCPF/RG: 16166869878 / 124311730\r\nEMISSAO: 27/03/19 - 11:29:14 IMPRESSAO: 27/03/19 - 11:29:35 [ OFFOS ]\r\nVENDEDOR: 12011 - LUIS HENRIQUE BACCINELLO\r\nCONCES: OPEN POINT DIST. VEICULOS LTDA DT. VENDA: 29/11/18\r\n.';
    $std->servico->codigomunicipio = 4106902;

    $std->servico->valores = new \stdClass();
    $std->servico->valores->valorservicos = 190.33;
    $std->servico->valores->valordeducoes = 0.00;
    $std->servico->valores->valorpis = 0.00;
    $std->servico->valores->valorcofins = 0.00;
    $std->servico->valores->valorinss = 0.00;
    $std->servico->valores->valorir = 0.00;
    $std->servico->valores->valorcsll = 0.00;
    $std->servico->valores->issretido = 1;
    $std->servico->valores->valoriss = 9.52;
    $std->servico->valores->valorissretido = 0.00;
    $std->servico->valores->outrasretencoes = 0.00;
    $std->servico->valores->BaseCalculo = 190.33;
    $std->servico->valores->aliquota = 0.05;
    $std->servico->valores->valorliquidonfse = 190.33;
    $std->servico->valores->descontoincondicionado = 0.00;
    $std->servico->valores->descontocondicionado = 0.00;

    $std->Intermediarioservico = new \stdClass();
    $std->Intermediarioservico->RazaoSocial = 'INSCRICAO DE TESTE SIATU - D AGUA -PAULINO S';
    $std->Intermediarioservico->Cnpj = '99999999000191';
    $std->Intermediarioservico->InscricaoMunicipal = '8041700010';
    $std->construcaocivil = new \stdClass();
    $std->construcaocivil->codigoobra = '1234';
    $std->construcaocivil->art = '1234';

    // Gerando o XML do RPS
    $rps = new Rps($std);
    $rps->config(json_decode($configJson));
    $xml = $rps->render();
    $xml = $tools->sign($xml, 'InfRps', '');
    
    $response = $tools->validarXml($xml);

    echo FakePretty::prettyPrint($response, '');
} catch (\Exception $e) {
    echo $e->getMessage();
}
