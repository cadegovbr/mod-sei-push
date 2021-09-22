<?

require_once dirname(__FILE__).'/../../../SEI.php';

class PushRelNotificacaoTarefaBD extends InfraBD {
  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }
  
}
?>