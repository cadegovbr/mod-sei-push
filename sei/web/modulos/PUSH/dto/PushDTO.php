<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_push';
  }
  
  public function montar() {
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdPush', 'id_md_push');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdProcedimento', 'id_procedimento');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Email', 'email');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Chave', 'chave');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'UltimoEnvioEmail', 'dth_ultimo_envio_email');
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjAtividadesDTO');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjEventosHabilitadosDTO');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NumeroProtocoloFormatado', 'protocolo_formatado', 'protocolo');
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'StaNivelAcessoGlobal', 'sta_nivel_acesso_global', 'protocolo');
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento', 'id_tipo_procedimento', 'procedimento');
    
    $this->configurarPK('IdPush', InfraDTO::$TIPO_PK_NATIVA);
    $this->configurarFK('IdProcedimento', 'procedimento', 'id_procedimento', InfraDTO::$TIPO_FK_OBRIGATORIA);
    $this->configurarFK('IdProcedimento', 'protocolo', 'id_protocolo', InfraDTO::$TIPO_FK_OBRIGATORIA);
  }
}
?>