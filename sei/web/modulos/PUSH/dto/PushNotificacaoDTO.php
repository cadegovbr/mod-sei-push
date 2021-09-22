<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushNotificacaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_push_notificacao';
  }
  
  public function montar() {
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdNotificacao', 'id_notificacao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento', 'id_tipo_procedimento');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdEmailSistema', 'id_email_sistema');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinNotificaRestrito', 'sin_notifica_restrito');

    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjTarefaDTO');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'PalavrasPesquisa');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeTipoProcedimento', 'nome', 'tipo_procedimento');
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoEmailSistema', 'descricao', 'email_sistema');

    
    
    $this->configurarPK('IdNotificacao', InfraDTO::$TIPO_PK_NATIVA);
    $this->configurarFK('IdTipoProcedimento', 'tipo_procedimento', 'id_tipo_procedimento', InfraDTO::$TIPO_FK_OBRIGATORIA);
    $this->configurarFK('IdEmailSistema', 'email_sistema', 'id_email_sistema', InfraDTO::$TIPO_FK_OBRIGATORIA);
  }
}
?>