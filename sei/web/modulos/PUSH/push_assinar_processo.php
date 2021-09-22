<?
require_once dirname(__FILE__).'/../../SEI.php';

SessaoSEIExterna::getInstance()->validarSessao();

try{
  $strResposta = '';
  if($_GET['idProcedimento'] != null){
    $objProtocoloDTO = new ProtocoloDTO();
    $objProtocoloDTO->setDblIdProtocolo($_GET['idProcedimento']);
    $objProtocoloDTO->retStrProtocoloFormatado();
    $objProtocoloRN = new ProtocoloRN();
    $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

    if($objProtocoloDTO != null){
      $strTitulo = 'Assinar processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' no PUSH';
      if($_POST['txtNome'] != null && $_POST['txtEmail']){
        $objPushDTO = new PushDTO();
        $objPushDTO->setDblIdProcedimento($_GET['idProcedimento']);
        $objPushDTO->setStrNome($_POST['txtNome']);
        $objPushDTO->setStrEmail($_POST['txtEmail']);

        if($objPushDTO->getDblIdProcedimento() != '' && InfraUtil::validarEmail($objPushDTO->getStrEmail()) && $objPushDTO->getStrNome() != ''){
          $objPushRN = new PushRN();
          $objPushRN->cadastrar($objPushDTO);
          $strResposta = 'Registro incluído no PUSH. Um e-mail foi enviado para o endereço de email informado para confirmação do cadastro.';
        }
        else
          $strResposta = 'Dados de cadastro inválidos';
      }
    }
    else
      $strResposta = 'Dados de cadastro inválidos'; 
  }
  else
    $strResposta = 'ID do Processo inválido';

}catch(Exception $e){
  if( $e->__toString() == 'Já existe um registro no PUSH com o e-mail informado para o processo escolhido')
    $strResposta = $e->__toString();
  else
    $strResposta = "Ocorreu um erro durante o cadastro no PUSH ";
}

PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: '.PaginaSEIExterna::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>

div.infraBarraSistemaE {width:90%}
div.infraBarraSistemaD {width:5%}
div.infraBarraComandos {width:99%}

table caption {
  text-align:left !important;
  font-size: 1.2em;
  font-weight:bold;
}

.andamentoAberto {
  background-color:white;
}

.andamentoConcluido {
  background-color:white;
}


#tblCabecalho{margin-top:1;}
#tblDocumentos {margin-top:1.5em;}
#tblHistorico {margin-top:1.5em;}
#divInfraAreaPaginacaoSuperior {display: none;}
#divinfraAndamentoAreaPaginacaoSuperior {display: none;}

span.retiraAncoraPadraoAzul{font-size: 1.2em;}

<?
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
PaginaSEIExterna::getInstance()->montarBarraComandosSuperior(array());

if($strResposta == '')
  echo PushFormINT::formularioPushPesquisa($_GET['idProcedimento']);
else
  echo PushFormINT::insereValor(utf8_decode($strResposta),true);

PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>