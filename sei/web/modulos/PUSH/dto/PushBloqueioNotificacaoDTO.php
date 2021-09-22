<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushBloqueioNotificacaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_push_bloqueio_notificacao';
  }
  
  public function montar() {
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdPushBloqueioNotificacao', 'id_md_push_bloqueio_notificacao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdProcedimento', 'id_procedimento');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NumeroProtocoloFormatado', 'protocolo_formatado', 'protocolo');

    $this->configurarPK('IdPushBloqueioNotificacao', InfraDTO::$TIPO_PK_NATIVA);
    $this->configurarFK('IdProcedimento', 'procedimento', 'id_procedimento', InfraDTO::$TIPO_FK_OBRIGATORIA);
    $this->configurarFK('IdProcedimento', 'protocolo', 'id_protocolo', InfraDTO::$TIPO_FK_OBRIGATORIA);
  }
}
?>