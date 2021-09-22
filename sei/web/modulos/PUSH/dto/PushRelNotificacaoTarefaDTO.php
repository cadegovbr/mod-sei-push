<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushRelNotificacaoTarefaDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_push_rel_notificacao_tarefa';
  }
  
  public function montar() {
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdRelNotificacaoTarefa', 'id_rel_notificacao_tarefa');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdNotificacao', 'id_notificacao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTarefa', 'id_tarefa');

    
    $this->configurarPK('IdRelNotificacaoTarefa', InfraDTO::$TIPO_PK_NATIVA);
    $this->configurarFK('IdNotificacao', 'md_push_notificacao', 'id_notificacao', InfraDTO::$TIPO_FK_OBRIGATORIA);
    $this->configurarFK('IdTarefa', 'tarefa', 'id_tarefa', InfraDTO::$TIPO_FK_OBRIGATORIA);
  }
}
?>