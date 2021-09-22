<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushConfiguracaoINT extends InfraINT {

  public static function montarSelectPushConfiguracao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objPushConfiguracaoDTO = new PushConfiguracaoDTO();
    $objPushConfiguracaoDTO->retNumIdPushConfiguracao();
    $objPushConfiguracaoDTO->retStrNome();
    
    $objPushConfiguracaoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objPushConfiguracaoRN = new PushConfiguracaoRN();
    $arrObjPushConfiguracaoDTO = $objPushConfiguracaoRN->listar($objPushConfiguracaoDTO);
    
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjPushConfiguracaoDTO, 'IdPushConfiguracao', 'Nome');
  }
}
?>