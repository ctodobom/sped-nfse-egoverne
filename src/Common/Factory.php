<?php

namespace NFePHP\NFSeEGoverne\Common;

/**
 * Class for RPS XML convertion
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

use stdClass;
use NFePHP\Common\DOMImproved as Dom;
use DOMNode;
use DOMElement;

class Factory
{
    /**
     * @var \stdClass
     */
    protected $std;
    /**
     * @var Dom
     */
    protected $dom;
    /**
     * @var \DOMElement
     */
    protected $rps;
    /**
     * @var \stdClass
     */
    protected $config;
    
    /**
     * Constructor
     * 
     * @param \stdClass $std
     */
    public function __construct(stdClass $std)
    {
        $this->std = $std;

        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
        $this->rps = $this->dom->createElement('Rps');
    }

    /**
     * Add config
     * @param \stdClass $config
     */
    public function addConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Builder, converts sdtClass Rps in XML Rps
     * NOTE: without Prestador Tag
     * 
     * @return string RPS in XML string format
     */
    public function render()
    {
        $infRps = $this->dom->createElement('InfRps');

        $this->addIdentificacao($infRps);

        $this->dom->addChild(
            $infRps,
            "DataEmissao",
            $this->std->dataemissao,
            true
        );
        $this->dom->addChild(
            $infRps,
            "NaturezaOperacao",
            $this->std->naturezaoperacao,
            true
        );
        $this->dom->addChild(
            $infRps,
            "RegimeEspecialTributacao",
            $this->std->regimeespecialtributacao ?? null,
            false
        );
        $this->dom->addChild(
            $infRps,
            "OptanteSimplesNacional",
            $this->std->optantesimplesnacional,
            true
        );
        $this->dom->addChild(
            $infRps,
            "IncentivadorCultural",
            $this->std->incentivadorcultural,
            true
        );
        $this->dom->addChild(
            $infRps,
            "Status",
            $this->std->status,
            true
        );
        
        $this->addRpsSubstituido($infRps);
        $this->addServico($infRps);
        $this->addPrestador($infRps);
        $this->addTomador($infRps);
        $this->addIntermediario($infRps);
        $this->addConstrucao($infRps);

        $this->rps->appendChild($infRps);
        $this->dom->appendChild($this->rps);
        return $this->dom->saveXML();
    }

    /**
     * Includes Identificacao TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addIdentificacao(&$parent)
    {
        $id = $this->std->identificacaorps;
        $node = $this->dom->createElement('IdentificacaoRps');
        $this->dom->addChild(
            $node,
            "Numero",
            $id->numero,
            true
        );
        $this->dom->addChild(
            $node,
            "Serie",
            $id->serie,
            true
        );
        $this->dom->addChild(
            $node,
            "Tipo",
            $id->tipo,
            true
        );
        $parent->appendChild($node);
    }
    
    /**
     * Tag RpsSubstituido
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addRpsSubstituido(&$parent)
    {
        if (empty($this->std->rpssubstituido)) {
            return;
        } 
        $subs = $this->std->rpssubstituido;
        $node = $this->dom->createElement('RpsSubstituido');
        $this->dom->addChild(
            $node,
            "Numero",
            $subs->numero,
            true
        );
        $this->dom->addChild(
            $node,
            "Serie",
            $subs->serie,
            true
        );
        $this->dom->addChild(
            $node,
            "Tipo",
            $subs->tipo,
            true
        );
        $parent->appendChild($node);
    }

    /**
     * Includes Servico TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addServico(&$parent)
    {
        $serv = $this->std->servico;
        $val = $this->std->servico->valores;
        $node = $this->dom->createElement('Servico');
        $valnode = $this->dom->createElement('Valores');
        $this->dom->addChild(
            $valnode,
            "ValorServicos",
            number_format($val->valorservicos, 2, '.', ''),
            true
        );
        $this->dom->addChild(
            $valnode,
            "ValorDeducoes",
            isset($val->valordeducoes)
                ? number_format($val->valordeducoes, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorPis",
            isset($val->valorpis)
                ? number_format($val->valorpis, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorCofins",
            isset($val->valorcofins)
                ? number_format($val->valorcofins, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorInss",
            isset($val->valorinss)
                ? number_format($val->valorinss, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorIr",
            isset($val->valorir)
                ? number_format($val->valorir, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorCsll",
            isset($val->valorcsll)
                ? number_format($val->valorcsll, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "IssRetido",
            $val->issretido,
            true
        );
        $this->dom->addChild(
            $valnode,
            "ValorIss",
            isset($val->valoriss)
                ? number_format($val->valoriss, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorIssRetido",
            isset($val->valorissretido)
                ? number_format($val->valorissretido, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "OutrasRetencoes",
            isset($val->outrasretencoes)
                ? number_format($val->outrasretencoes, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "BaseCalculo",
            isset($val->basecalculo)
                ? number_format($val->basecalculo, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "Aliquota",
            isset($val->aliquota) ? $val->aliquota : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorLiquidoNfse",
            isset($val->valorliquidonfse)
                ? number_format($val->valorliquidonfse, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "DescontoIncondicionado",
            isset($val->descontoincondicionado)
                ? number_format($val->descontoincondicionado, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "DescontoCondicionado",
            isset($val->descontocondicionado)
                ? number_format($val->descontocondicionado, 2, '.', '')
                : null,
            false
        );
        $node->appendChild($valnode);

        $this->dom->addChild(
            $node,
            "ItemListaServico",
            $serv->itemlistaservico,
            true
        );
        $this->dom->addChild(
            $node,
            "CodigoCnae",
            isset($serv->codigocnae)
                ? $serv->codigocnae
                : null,
            false
        );
        $this->dom->addChild(
            $node,
            "CodigoTributacaoMunicipio",
            isset($serv->codigotributacaomunicipio)
                ? $serv->codigotributacaomunicipio
                : null,
            false
        );
        $this->dom->addChild(
            $node,
            "Discriminacao",
            $serv->discriminacao,
            true
        );
        $this->dom->addChild(
            $node,
            "CodigoMunicipio",
            $serv->codigomunicipio,
            true
        );
        $parent->appendChild($node);
    }

    /**
     * Includes Prestador TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     *  @return void
     */
    protected function addPrestador(&$parent)
    {
        if (!isset($this->config)) {
            return;
        }
        $node = $this->dom->createElement('Prestador');
        if (isset($this->config->cnpj)) {
            $this->dom->addChild(
                $node,
                "Cnpj",
                isset($this->config->cnpj) ? $this->config->cnpj : null,
                false
            );
        } else {
            $this->dom->addChild(
                $node,
                "Cpf",
                isset($this->config->cpf) ? $this->config->cpf : null,
                false
            );
        }
        $this->dom->addChild(
            $node,
            "InscricaoMunicipal",
            isset($this->config->im) ? $this->config->im : null,
            false
        );
        $parent->appendChild($node);
    }

    /**
     * Includes Tomador TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addTomador(&$parent)
    {
        if (!isset($this->std->tomador)) {
            return;
        }
        $tom = $this->std->tomador;
        $end = $this->std->tomador->endereco;
        $node = $this->dom->createElement('Tomador');
        $ide = $this->dom->createElement('IdentificacaoTomador');
        if (! empty($tom->cnpj) || ! empty($tom->cpf)) {
            $cpfcnpj = $this->dom->createElement('CpfCnpj');
            $this->dom->addChild(
                $cpfcnpj,
                "Cnpj",
                $tom->cnpj ?? null,
                false
            );
            $this->dom->addChild(
                $cpfcnpj,
                "Cpf",
                $tom->cpf ?? null,
                false
            );
            $ide->appendChild($cpfcnpj);
        }    
        $this->dom->addChild(
            $ide,
            "InscricaoMunicipal",
            $tom->inscricaomunicipal ?? null,
            false
        );
        $node->appendChild($ide);
        $this->dom->addChild(
            $node,
            "RazaoSocial",
            $tom->razaosocial ?? null,
            false
        );
        $endereco = $this->dom->createElement('Endereco');
        $this->dom->addChild(
            $endereco,
            "Endereco",
            $end->endereco ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Numero",
            $end->numero ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Complemento",
            $end->complemento ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Bairro",
            $end->bairro ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "CodigoMunicipio",
            $end->codigomunicipio ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Uf",
            $end->uf ?? null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Cep",
            $end->cep ?? null,
            false
        );
        $node->appendChild($endereco);
        if (! empty($tom->telefone) || ! empty($tom->email)) {
            $contato = $this->dom->createElement('Contato');
            $this->dom->addChild(
                $contato,
                "Telefone",
                $tom->telefone ?? null,
                false
            );
            $this->dom->addChild(
                $contato,
                "Email",
                $tom->email ?? null,
                false
            );
            $node->appendChild($contato);
        }
        $parent->appendChild($node);
    }

    /**
     * Includes Intermediario TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addIntermediario(&$parent)
    {
        if (!isset($this->std->intermediarioservico)) {
            return;
        }
        $int = $this->std->intermediarioservico;
        $node = $this->dom->createElement('IntermediarioServico');
        $this->dom->addChild(
            $node,
            "RazaoSocial",
            $int->razaosocial,
            true
        );
        if (! empty($int->cnpj) || ! empty($int->cpf)) {
            $cpfcnpj = $this->dom->createElement('CpfCnpj');
            $this->dom->addChild(
                $cpfcnpj,
                "Cnpj",
                $int->cnpj ?? null,
                false
            );
            $this->dom->addChild(
                $cpfcnpj,
                "Cpf",
                $int->cpf ?? null,
                false
            );
            $node->appendChild($cpfcnpj);
        }
        $this->dom->addChild(
            $node,
            "InscricaoMunicipal",
            $int->inscricaomunicipal ?? null,
            false
        );
        $parent->appendChild($node);
    }

    /**
     * Includes Construcao TAG in parent NODE
     * 
     * @param \DOMElement $parent
     * 
     * @return void
     */
    protected function addConstrucao(&$parent)
    {
        if (!isset($this->std->construcaocivil)) {
            return;
        }
        $obra = $this->std->construcaocivil;
        $node = $this->dom->createElement('ContrucaoCivil');
        $this->dom->addChild(
            $node,
            "CodigoObra",
            $obra->codigoobra,
            true
        );
        $this->dom->addChild(
            $node,
            "Art",
            $obra->art,
            true
        );
        $parent->appendChild($node);
    }
}
