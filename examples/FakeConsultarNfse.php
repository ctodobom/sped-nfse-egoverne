<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Common\Soap\SoapFake;
use NFePHP\NFSeEGoverne\Common\FakePretty;

try {

    $config = [
        'cnpj' => '12371536000100',
        'im' => '170606466257',
        'cmun' => '4106902',
        'razao' => 'Empresa Test Ltda',
        'tpamb' => 1
    ];

    $configJson = json_encode($config);

    $content = file_get_contents('C:\Users\Cleiton\Downloads\nfse\curitiba\cert2.pfx');
    $password = 'ian2711';
    $cert = Certificate::readPfx($content, $password);

    $soap = new SoapFake();
    //$soap->disableCertValidation(true);

    $tools = new Tools($configJson, $cert);
    //$tools->loadSoapClass($soap);

    //Campos obrigatórios
    $filtro = new stdClass();
    $filtro->dataInicial = '2020-01-01';
    $filtro->dataFinal = '2020-01-30';

    //Opcional
    //$filtro->numeroNfse = 5555;

    //Opcional
    //$filtro->tomador = new stdClass();
    //$filtro->tomador->cpf = null;
    //$filtro->tomador->cnpj = '12345678901234';
    //$filtro->tomador->inscricaoMunicipal = null;

    $response = $tools->consultarNfse($filtro);
    header("Content-type: text/plain");
    echo $response;

    //echo FakePretty::prettyPrint($response, '');
} catch (\Exception $e) {
    echo $e->getMessage();
}
