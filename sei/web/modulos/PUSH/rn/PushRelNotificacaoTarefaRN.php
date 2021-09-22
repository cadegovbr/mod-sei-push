<?
require_once dirname(__FILE__). '/../../../SEI.php';

class PushRelNotificacaoTarefaRN extends InfraRN {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance();
	}
	
	protected function cadastrarControlado(PushRelNotificacaoTarefaDTO $objPushRelNotificacaoTarefaDTO) {
		try {
			$objPushRelNotificacaoTarefaBD = new PushRelNotificacaoTarefaBD($this->getObjInfraIBanco());
			$objPushRelNotificacaoTarefaBD->cadastrar($objPushRelNotificacaoTarefaDTO);
		} catch(Exception $e){
			throw new InfraException('Erro cadastrando registro de Notificação do PUSH.', $e);
		}
	}

    protected function alterarControlado(PushRelNotificacaoTarefaDTO $objPushRelNotificacaoTarefaDTO) {
        try {
            $objPushRelNotificacaoTarefaBD = new PushRelNotificacaoTarefaBD($this->getObjInfraIBanco());
			$objPushRelNotificacaoTarefaBD->alterar($objPushRelNotificacaoTarefaDTO);
        }
        catch (Exception $e) {
            throw new InfraException ('Erro alterando registro de Notificação do PUSH.', $e);
        }
    }
	
	protected function excluirControlado(PushRelNotificacaoTarefaDTO $objPushRelNotificacaoTarefaDTO) {
		try {
			$objPushRelNotificacaoTarefaBD = new PushRelNotificacaoTarefaBD($this->getObjInfraIBanco());
			$objPushRelNotificacaoTarefaBD->excluir($objPushRelNotificacaoTarefaDTO);
		} catch(Exception $e){
			throw new InfraException('Erro excluindo registro de Notificação do PUSH.', $e);
		}
	}
	
	protected function listarConectado(PushRelNotificacaoTarefaDTO $objPushRelNotificacaoTarefaDTO) {
		try {
			$objPushRelNotificacaoTarefaBD = new PushRelNotificacaoTarefaBD($this->getObjInfraIBanco());
			return $objPushRelNotificacaoTarefaBD->listar($objPushRelNotificacaoTarefaDTO);
		} catch(Exception $e){
			throw new InfraException('Erro listando registro de Notificação do PUSH.', $e);
		}
	}
	
	protected function consultarConectado(PushRelNotificacaoTarefaDTO $objPushRelNotificacaoTarefaDTO) {
		try {
			$objPushRelNotificacaoTarefaBD = new PushRelNotificacaoTarefaBD($this->getObjInfraIBanco());
			return $objPushRelNotificacaoTarefaBD->consultar($objPushRelNotificacaoTarefaDTO);
		} catch(Exception $e){
			throw new InfraException('Erro consultando registro do PUSH.', $e);
		}
	}
}
?>