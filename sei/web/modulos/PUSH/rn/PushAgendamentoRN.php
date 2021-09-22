<?

require_once dirname(__FILE__). '/../../../SEI.php';

class PushAgendamentoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
  
  public function enviarEmailPush(){
    try{
      ini_set('max_execution_time','0');
      ini_set('memory_limit','1024M');
  
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(false);
      InfraDebug::getInstance()->limpar();
  
      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, SessaoSEI::$UNIDADE_TESTE);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objPushRN = new PushRN();
      //InfraDebug::getInstance()->gravar('GRAVANDO E-MAILS DO MÓDULO PUSH NA TABELA DE CONTROLE');
      //$objPushRN->gravarEmailControle();
      InfraDebug::getInstance()->gravar('ENVIANDO E-MAILS DO MÓDULO PUSH');
      $objPushRN->enviarEmailsPeriodico();

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
      InfraDebug::getInstance()->gravar('TEMPO TOTAL DE EXECUCAO: '.$numSeg.' s');
      InfraDebug::getInstance()->gravar('FIM');
  
      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(),InfraLog::$INFORMACAO);

    }catch(Exception $e){
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(false);
  
      throw new InfraException('Erro enviando e-mails do PUSH.',$e);
    }
  }

}
?>