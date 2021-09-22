<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushConfiguracaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_push_config';
  }
  
  public function montar() {
	
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdPushConfiguracao', 'id_md_push_config');
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
  	$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Valor', 'valor');

  	$this->configurarPK('IdPushConfiguracao',InfraDTO::$TIPO_PK_NATIVA);

  }
}
?>