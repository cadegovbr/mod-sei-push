<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushConfiguracaoRN extends InfraRN {

  public function __construct() {
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco() {
    return BancoSEI::getInstance();
  }

  protected function cadastrarControlado(PushConfiguracaoDTO $objPushConfiguracaoDTO) {
    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_cadastrar', __METHOD__, $objPushConfiguracaoDTO);

      $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());

      return $objPushConfiguracaoBD->cadastrar($objPushConfiguracaoDTO);
    }
    catch (Exception $e) {
      throw new InfraException ('Erro cadastrando configuração.', $e);
    }
  }

  protected function alterarControlado(PushConfiguracaoDTO $objPushConfiguracaoDTO) {
    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_alterar', __METHOD__, $objPushConfiguracaoDTO);

      $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());

      return $objPushConfiguracaoBD->alterar($objPushConfiguracaoDTO);
    }
    catch (Exception $e) {
      throw new InfraException ('Erro alterando configuração.', $e);
    }
  }

  protected function excluirControlado($arrObjPushConfiguracaoDTO) {
    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_excluir', __METHOD__, $arrObjPushConfiguracaoDTO);

      if (count($arrObjPushConfiguracaoDTO) > 0) {
        $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());
        
        for ($i = 0; $i < count($arrObjPushConfiguracaoDTO); $i++)
          $objPushConfiguracaoBD->excluir($arrObjPushConfiguracaoDTO[$i]);
      }
    }
    catch (Exception $e) {
      throw new InfraException ('Erro excluindo configuração.', $e);
    }
  }
  
  protected function listarConectado(PushConfiguracaoDTO $objPushConfiguracaoDTO) {

    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_listar', __METHOD__, $objPushConfiguracaoDTO);

      $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());

      return $objPushConfiguracaoBD->listar($objPushConfiguracaoDTO);
    }
    catch (Exception $e) {
      throw new InfraException('Erro listando configuração.', $e);
    }
  }

  protected function consultarConectado(PushConfiguracaoDTO $objPushConfiguracaoDTO) {
    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_consultar', __METHOD__, $objPushConfiguracaoDTO);

      $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());

      return $objPushConfiguracaoBD->consultar($objPushConfiguracaoDTO);
    }
    catch (Exception $e) {
      throw new InfraException('Erro consultando configuração.', $e);
    }
  }

  protected function pesquisarConectado(PushConfiguracaoDTO $objPushConfiguracaoDTO) {
    try {
      SessaoSEI::getInstance()->validarAuditarPermissao('push_config_listar',__METHOD__,$objPushConfiguracaoDTO);

      if ($objPushConfiguracaoDTO->isSetStrNome())
        $objPushConfiguracaoDTO->setStrNome('%'.$objPushConfiguracaoDTO->getStrNome().'%',InfraDTO::$OPER_LIKE);

      $objPushConfiguracaoBD = new PushConfiguracaoBD($this->getObjInfraIBanco());

      return $objPushConfiguracaoBD->listar($objPushConfiguracaoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando configuração.',$e);
    }
  }
}
?>