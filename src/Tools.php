<?php

namespace NFePHP\NFSeEGoverne;

/**
 * Class for comunications with NFSe webserver in Nacional Standard
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

use NFePHP\NFSeEGoverne\Common\Tools as BaseTools;
use NFePHP\NFSeEGoverne\RpsInterface;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Certificate;
use NFePHP\Common\Validator;

class Tools extends BaseTools
{
    const ERRO_EMISSAO = 1;
    const SERVICO_NAO_CONCLUIDO = 2;
    
    protected $xsdpath;
    
    public function __construct($config, Certificate $cert)
    {
        parent::__construct($config, $cert);
        $path = realpath(
            __DIR__ . '/../storage/schemes'
        );
        
        if (file_exists($this->xsdpath = $path . '/'.$this->config->cmun.'.xsd')) {
            $this->xsdpath = $path . '/'.$this->config->cmun.'.xsd';
        } else {
            $this->xsdpath = $path . '/nfse_v20_08_2015.xsd';
        }
    }
    
    /**
     * Solicita o cancelamento de NFSe (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=CancelarNfse
     * @param string $id
     * @param integer $numero
     * @param integer $codigo
     * @return string
     */
    public function cancelarNfse($id, $numero, $codigo = self::ERRO_EMISSAO)
    {
        $operation = 'CancelarNfse';
        $pedido = "<Pedido>"
            . "<InfPedidoCancelamento>"
            . "<IdentificacaoNfse>"
            . "<Numero>$numero</Numero>"
            . "<Cnpj>" . $this->config->cnpj . "</Cnpj>"
            . "<InscricaoMunicipal>" . $this->config->im . "</InscricaoMunicipal>"
            . "<CodigoMunicipio>" . $this->config->cmun . "</CodigoMunicipio>"
            . "</IdentificacaoNfse>"
            . "<CodigoCancelamento>$codigo</CodigoCancelamento>"
            . "</InfPedidoCancelamento>"
            . "</Pedido>";
        
        $signed = $this->sign($pedido, 'InfPedidoCancelamento', '');
        $content = "<CancelarNfseEnvio xmlns=\"{$this->wsobj->msgns}/\">"
            . "<CancelarNfseEnvio>"
            . $signed
            . "</CancelarNfseEnvio>"
            . "</CancelarNfseEnvio>";
        Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta Lote RPS (SINCRONO) após envio com recepcionarLoteRps() (ASSINCRONO)
     * complemento do processo de envio assincono.
     * Que deve ser usado quando temos mais de um RPS sendo enviado
     * por vez.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarLoteRps
     * @param string $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo)
    {
        $operation = 'ConsultarLoteRps';
        $content = "<ConsultarLoteRps xmlns=\"{$this->wsobj->msgns}/\">"
            . "<ConsultarLoteRpsEnvio>"
            . $this->prestador
            . "<Protocolo>$protocolo</Protocolo>"
            . "</ConsultarLoteRpsEnvio>"
            . "</ConsultarLoteRps>";
        Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta NFSe emitidas em um periodo e por tomador (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfse
     * @param string $dini
     * @param string $dfim
     * @param string $tomadorCnpj
     * @param string $tomadorCpf
     * @param string $tomadorIM
     * @return string
     */
    public function consultarNfse($dini, $dfim, $tomadorCnpj = null, $tomadorCpf = null, $tomadorIM = null, $numeroNFSe = null, $intermediario = null)
    {
        $operation = 'ConsultarNfse';
        $content = "<ConsultarNfse xmlns=\"{$this->wsobj->msgns}/\">"
            . "<ConsultarNfseEnvio>"
            . $this->prestador
            . "<NumeroNfse>$numeroNFSe<NumeroNfse>"
            . "<PeriodoEmissao>"
            . "<DataInicial>$dini</DataInicial>"
            . "<DataFinal>$dfim</DataFinal>"
            . "</PeriodoEmissao>";
            
        if ($tomadorCnpj || $tomadorCpf) {
            $content .= "<Tomador>"
            . "<CpfCnpj>";
            if (isset($tomadorCnpj)) {
                $content .= "<Cnpj>$tomadorCnpj</Cnpj>";
            } else {
                $content .= "<Cpf>$tomadorCpf</Cpf>";
            }
            $content .= "</CpfCnpj>";
            if (isset($tomadorIM)) {
                $content .= "<InscricaoMunicipal>$tomadorIM</InscricaoMunicipal>";
            }
            $content .= "</Tomador>";
        }

        $content .= $intermediario;

        $content .= "</ConsultarNfseEnvio>"
            . "</ConsultarNfse>";
        Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta NFSe por RPS (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfsePorRps
     * @param integer $numero
     * @param string $serie
     * @param integer $tipo
     * @return string
     */
    public function consultarNfsePorRps($numero, $serie, $tipo)
    {
        $operation = "ConsultarNfsePorRps";
        $content = "<ConsultarNfseRpsEnvio xmlns=\"{$this->wsobj->msgns}/\">"
            . "<IdentificacaoRps>"
            . "<Numero>$numero</Numero>"
            . "<Serie>$serie</Serie>"
            . "<Tipo>$tipo</Tipo>"
            . "</IdentificacaoRps>"
            . $this->prestador
            . "</ConsultarNfseRpsEnvio>";
        //Validator::isValid($content, $this->xsdpath); // <-- ERRO: This XML is not valid. 
                                                        //     Element 'ConsultarNfsePorRps': 
                                                        //     No matching global declaration available for the validation root.
        return $this->send($content, $operation);
    }
    
    /**
     * Envia LOTE de RPS para emissão de NFSe (ASSINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarLoteRps
     * @param array $arps Array contendo de 1 a 50 RPS::class
     * @param string $lote Número do lote de envio
     * @return string
     * @throws \Exception
     */
    public function recepcionarLoteRps($arps, $lote)
    {
        $operation = 'RecepcionarLoteRps';
        $no_of_rps_in_lot = count($arps);
        if ($no_of_rps_in_lot > 50) {
            throw new \Exception('O limite é de 50 RPS por lote enviado.');
        }
        $content = '';
        foreach ($arps as $rps) {
            $xml = $rps->render();
            $xmlsigned = $this->sign($xml, 'InfRps', '');
            $content .= $xmlsigned;
        }
        $contentmsg = "<EnviarLoteRpsEnvio xmlns=\"{$this->wsobj->msgns}/\">"
            . "<LoteRps>"
            . "<NumeroLote>$lote</NumeroLote>"
            . "<Cnpj>" . $this->config->cnpj . "</Cnpj>"
            . "<InscricaoMunicipal>" . $this->config->im . "</InscricaoMunicipal>"
            . "<QuantidadeRps>$no_of_rps_in_lot</QuantidadeRps>"
            . "<ListaRps>"
            . $content
            . "</ListaRps>"
            . "</LoteRps>"
            . "</EnviarLoteRpsEnvio>";

        $content = $this->sign($contentmsg, 'LoteRps', '');
        
        //Validator::isValid($content, $this->xsdpath); // <-- ERRO: This XML is not valid. 
                                                        //     Element 'EnviarLoteRpsEnvio': 
                                                        //     No matching global declaration available for the validation root.
        //echo $this->validarXml($content);             // <-- Nao reconheceu o conteudo...
        return $this->send($content, $operation);
    }
    
    /**
     * Buscar Usuario (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=BuscarUsuario
     * @param string $cnpj
     * @param string $imu
     * @return string
     */
    public function buscarUsuario($cnpj, $imu)
    {
        $operation = 'BuscarUsuario';
        $content = "<BuscarUsuario xmlns=\"{$this->wsobj->msgns}/\">"
            . "<imu>$imu</imu>"
            . "<cnpj>$cnpj</cnpj>"
            . "</BuscarUsuario>";
        //Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    
    /**
     * Recepcionar Xml (SINCRONO)
     * Parâmentro (metodo) nome do metodo WS que será chamado. 
     * Os valores podem ser : (RecepcionarLoteRps, ConsultarSituacaoLoteRps, ConsultarNfsePorRps, 
     *                         ConsultarNfse, ConsultarLoteRps e CancelarNfse) 
     * e o Parâmetro (xml) deve ser a mensagem xml a ser enviada.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarXml
     * @param string $metodo
     * @param string $xml
     * @return string
     */
    public function recepcionarXml($metodo, $xml)
    {
        $operation = 'RecepcionarXml';
        $content = "<RecepcionarXml xmlns=\"{$this->wsobj->msgns}/\">"
            . "<metodo>$metodo</metodo>"
            . "<xml>$xml</xml>"
            . "</RecepcionarXml>";

        //Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    
    /**
     * Validar Xml (SINCRONO)
     * Realiza a validação básica de um xml de acordo com o schema xsd
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ValidarXml
     * @param string $xml
     * @return string
     */
    public function validarXml($xml)
    {
        $operation = 'ValidarXml';
        $content = "<ValidarXml xmlns=\"{$this->wsobj->msgns}/\">"
            . "<xml>$xml</xml>"
            . "</ValidarXml>";

        //$dom = new Dom('1.0', 'UTF-8');
        //$dom->preserveWhiteSpace = false;
        //$dom->formatOutput = false;
        //$dom->loadXML($contdata);

        //$node = $dom->getElementsByTagName('xml')->item(0);
        //$cdata = $dom->createCDATASection($xml);
        //$node->appendChild($cdata);

        //$content = $dom->saveXML($dom->documentElement);


        //Validator::isValid($content, $this->xsdpath);
        return $this->send($content, $operation);
    }
    

}
