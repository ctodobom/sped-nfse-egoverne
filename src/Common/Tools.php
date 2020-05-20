<?php

namespace NFePHP\NFSeEGoverne\Common;

/**
 * Auxiar Tools Class for comunications with NFSe webserver in Nacional Standard
 *
 * @category  NFePHP
 * @package   NFePHP\NFSeEGoverne
 * @copyright NFePHP Copyright (c) 2008-2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse-egoverne for the canonical source repository
 */

use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\RpsInterface;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\NFSeEGoverne\Common\Signer;
use NFePHP\NFSeEGoverne\Common\Soap\SoapInterface;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;

class Tools
{
    /**
     * @var string
     */
    public $lastRequest;
    /**
     * @var \stdClass
     */
    protected $config;
    /**
     * @var type 
     */
    protected $prestador;
    /**
     * @var \NFePHP\Common\Certificate
     */
    protected $certificate;
    /**
     * @var \stdClass
     */
    protected $wsobj;
    /**
     * @var \NFePHP\NFSeEGoverne\Common\Soap\SoapInterface
     */
    protected $soap;
    /**
     * @var string
     */
    protected $environment;
    /**
     * @var bool
     */
    protected $enableSync = false;

    /**
     * Constructor
     * @param string $config
     * @param Certificate $cert
     */
    public function __construct($config, Certificate $cert)
    {
        $this->config = json_decode($config);
        $this->certificate = $cert;
        $this->buildPrestadorTag();
        $this->wsobj = $this->loadWsobj($this->config->cmun);
        $this->environment = 'homologacao';
        if ($this->config->tpamb === 1) {
            $this->environment = 'producao';
        }
    }
    
    /**
     * Habilita ou desabilita o envio de RPS em modo SINCRONO
     * 
     * @param bool|null $flag
     * 
     * @return bool
     */
    public function enableSynchronous($flag = null)
    {
        if ($flag !== null) {  
            return $this->enableSync = $flag;
        }
        return $this->enableSync;
    }

    /**
     * load webservice parameters
     * @param string $cmun
     * @return object
     * @throws \Exception
     */
    protected function loadWsobj($cmun)
    {
        $path = realpath(__DIR__ . "/../../storage/urls_webservices.json");
        $urls = json_decode(file_get_contents($path), true);
        if (empty($urls[$cmun])) {
            throw new \Exception("Não localizado parâmetros para esse municipio.");
        }
        return (object)$urls[$cmun];
    }


    /**
     * SOAP communication dependency injection
     * @param SoapInterface $soap
     */
    public function loadSoapClass(SoapInterface $soap)
    {
        $this->soap = $soap;
    }

    /**
     * Build tag Prestador
     */
    protected function buildPrestadorTag()
    {
        $this->prestador = "<Prestador>"
            . "<Cnpj>" . $this->config->cnpj . "</Cnpj>"
            . "<InscricaoMunicipal>" . $this->config->im . "</InscricaoMunicipal>"
            . "</Prestador>";
    }

    /**
     * Sign XML passing in content
     * @param string $content
     * @param string $tagname
     * @param string $mark
     * @return string XML signed
     */
    public function sign($content, $tagname, $mark)
    {
        $xml = Signer::sign(
            $this->certificate,
            $content,
            $tagname,
            $mark
        );
        $dom = new Dom('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);
        return $dom->saveXML($dom->documentElement);
    }

    /**
     * Send message to webservice
     * @param string $message
     * @param string $operation
     * @return string XML response from webservice
     */
    public function send($message, $operation)
    {
        $action = "{$this->wsobj->soapns}/$operation";
        $url = $this->wsobj->homologacao;
        if ($this->environment === 'producao') {
            $url = $this->wsobj->producao;
        }
        $request = $this->createSoapRequest($message, $operation);
        $this->lastRequest = $request;

        if (empty($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }
        $msgSize = strlen($request);
        $parameters = [
            "Content-Type: text/xml;charset=UTF-8",
            "SOAPAction: \"$action\"",
            "Content-length: $msgSize"
        ];
        $response = (string)$this->soap->send(
            $operation,
            $url,
            $action,
            $request,
            $parameters
        );
        return $response;
    }

    /**
     * Build SOAP request
     * @param string $message
     * @param string $operation
     * @return string XML SOAP request
     */
    protected function createSoapRequest($message, $operation)
    {
        $env = "<soap:Envelope "
            . "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" "
            . "xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" "
            . "xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">"
            . "<soap:Body>"
            . "<{$operation} xmlns=\"{$this->wsobj->soapns}/\">"
            . $message
            . "</{$operation}>"
            . "</soap:Body>"
            . "</soap:Envelope>";

        $dom = new Dom('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($env);

        return $dom->saveXML($dom->documentElement);
    }
}
