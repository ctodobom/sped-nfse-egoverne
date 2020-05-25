<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

$ver = 'v1_00';

$jsonSchema = '
{
    "title": "RPS",
    "type": "object",
    "properties": {
        "version": {
            "required": true,
            "type": "string"
        },
        "identificacaorps": {
            "required": true,
            "type": "object",
            "properties": {
                "numero": {
                    "required": true,
                    "type": "string",
                    "pattern": "^[0-9]{1,15}"
                },
                "serie": {
                    "required": true,
                    "type": "string",
                    "pattern": "^[0-9A-Za-z]{1,5}$"
                },
                "tipo": {
                    "required": true,
                    "type": "integer",
                    "minimum": 1,
                    "maximum": 3
                }
            }
        },
        "dataemissao": {
            "required": true,
            "type": "string",
            "pattern": "^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])T(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9])$"
        },
        "naturezaoperacao": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 6
        },
        "regimeespecialtributacao": {
            "required": false,
            "type": ["integer","null"],
            "minimum": 1,
            "maximum": 6
        },
        "optantesimplesnacional": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 2
        },
        "incentivadorcultural": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 2
        },
        "status": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 2
        },
        "rpssubstituido": {
            "required": false,
            "type": ["object","null"],
            "properties": {
                "numero": {
                    "required": true,
                    "type": "string",
                    "pattern": "^[0-9]{1,15}"
                },
                "serie": {
                    "required": true,
                    "type": "string",
                    "pattern": "^[0-9A-Za-z]{1,5}$"
                },
                "tipo": {
                    "required": true,
                    "type": "integer",
                    "minumum": 1,
                    "maximum": 3
                }
            }
        },
        "tomador": {
            "required": false,
            "type": ["object","null"],
            "properties": {
                "cnpj": {
                    "required": false,
                    "type": ["string","null"],
                    "pattern": "^[0-9]{14}"
                },
                "cpf": {
                    "required": false,
                    "type": ["string","null"],
                    "pattern": "^[0-9]{11}"
                },
                "inscricaomunicipal": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 1,
                    "maxLength": 15
                },
                "razaosocial": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 1,
                    "maxLength": 115
                },
                "telefone": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 4,
                    "maxlength": 11 
                },
                "email": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 4,
                    "maxlength": 80
                },
                "endereco": {
                    "required": false,
                    "type": ["object","null"],
                    "properties": {
                        "endereco": {
                            "required": false,
                            "type": ["string","null"],
                            "minLength": 1,
                            "maxLength": 125
                        },
                        "numero": {
                            "required": false,
                            "type": ["string","null"],
                            "minLength": 1,
                            "maxLength": 10
                        },
                        "complemento": {
                            "required": false,
                            "type": ["string","null"],
                            "minLength": 1,
                            "maxLength": 60
                        },
                        "bairro": {
                            "required": false,
                            "type": ["string","null"],
                            "minLength": 1,
                            "maxLength": 60
                        },
                        "codigomunicipio": {
                            "required": false,
                            "type": ["string","null"],
                            "pattern": "^[0-9]{7}"
                        },
                        "uf": {
                            "required": false,
                            "type": ["string","null"],
                            "maxLength": 2
                        },
                        "cep": {
                            "required": false,
                            "type": ["string","null"],
                            "pattern": "^[0-9]{8}"
                        }
                    }
                }
            }
        },
        "servico": {
            "required": true,
            "type": "object",
            "properties": {
                "itemlistaservico": {
                    "required": true,
                    "type": "string",
                    "minLength": 1,
                    "maxLength": 5
                },
                "codigocnae": {
                    "required": false,
                    "type": ["string","null"],
                    "pattern": "^[0-9]{7}$"
                },
                "codigotributacaomunicipio": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 1,
                    "maxLength": 20
                },
                "discriminacao": {
                    "required": true,
                    "type": "string",
                    "minLength": 1,
                    "maxLength": 2000
                },
                "codigomunicipio": {
                    "required": true,
                    "type": "integer",
                    "pattern": "^[0-9]{7}"
                },
                "valores": {
                    "required": true,
                    "type": "object",
                    "properties": {
                        "valorservicos": {
                            "required": true,
                            "type": "number"
                        },
                        "valordeducoes": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorpis": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorcofins": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorinss": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorir": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorcsll": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "issretido": {
                            "required": true,
                            "type": "integer",
                            "minimum": 1,
                            "maximum": 2
                        },
                        "valoriss": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorissretido": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "outrasretencoes": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "basecalculo": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "aliquota": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "valorliquidonfse": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "descontoincondicionado": {
                            "required": false,
                            "type": ["number", "null"]
                        },
                        "descontocondicionado": {
                            "required": false,
                            "type": ["number", "null"]
                        }
                    }
                }
            }
        },
        "intermediarioservico": {
            "required": false,
            "type": ["object","null"],
            "properties": {
                "razaosocial": {
                    "required": true,
                    "type": "string",
                    "minLength": 1,
                    "maxLength": 115
                },
                "cnpj": {
                    "required": false,
                    "type": ["string","null"],
                    "pattern": "^[0-9]{14}"
                },
                "cpf": {
                    "required": false,
                    "type": ["string","null"],
                    "pattern": "^[0-9]{11}"
                },
                "inscricaomunicipal": {
                    "required": false,
                    "type": ["string","null"],
                    "minLength": 1,
                    "maxLength": 15
                }
            }
        },
        "construcaocivil": {
            "required": false,
            "type": ["object","null"],
            "properties": {
                "codigoobra": {
                    "required": true,
                    "type": "string",
                    "minLength": 1,
                    "maxLength": 15
                },
                "art": {
                    "required": true,
                    "type": "string",
                    "minLength": 1,
                    "maxLength": 15
                }
            }
        }
    }
}';

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

// Schema must be decoded before it can be used for validation
$jsonSchemaObject = json_decode($jsonSchema);
if (empty($jsonSchemaObject)) {
    echo "<h2>Erro de digitação no schema ! Revise</h2>";
    echo "<pre>";
    print_r($jsonSchema);
    echo "</pre>";
    die();
}
// The SchemaStorage can resolve references, loading additional schemas from file as needed, etc.
$schemaStorage = new SchemaStorage();
// This does two things:
// 1) Mutates $jsonSchemaObject to normalize the references (to file://mySchema#/definitions/integerData, etc)
// 2) Tells $schemaStorage that references to file://mySchema... should be resolved by looking in $jsonSchemaObject
$schemaStorage->addSchema('file://mySchema', $jsonSchemaObject);
// Provide $schemaStorage to the Validator so that references can be resolved during validation
$jsonValidator = new Validator(new Factory($schemaStorage));
// Do validation (use isValid() and getErrors() to check the result)
$jsonValidator->validate(
    $std, $jsonSchemaObject  //tenta converter o dado no tipo indicado no schema , Constraint::CHECK_MODE_COERCE_TYPES
);

if ($jsonValidator->isValid()) {
    echo "O JSON fornecido é validado no esquema.<br/>";
} else {
    echo "Dados não validados. Violações:<br/>";
    foreach ($jsonValidator->getErrors() as $error) {
        echo sprintf("[%s] %s<br/>", $error['property'], $error['message']);
    }
    die;
}
//salva se sucesso
file_put_contents("../storage/jsonSchemes/$ver/rps.schema", $jsonSchema);
