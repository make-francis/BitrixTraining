<?//quote/ajax
error_reporting( E_ALL );
ini_set('display_errors', 1);

function writeLog($message) {
  $handle = fopen($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/debug.log', 'a');
  $line   = '[' . date('Y-m-d H:i:s T') . '] ' . $message . "\n";
  fwrite($handle, $line);
  fclose($handle);
}


function writeToDB($drive_id, $quote_id, $contact_id, $email){
  if ($drive_id > 0 && strlen($email) > 0) :
    global $DB;
    $strSql = "INSERT INTO b_disk_crm_template (DRIVE_ID, QUOTE_ID, CONTACT_ID, EMAIL) VALUES (".$drive_id.", ".$quote_id.", ".$contact_id.", '".$email."')";
    writeLog("=================== QUERY ===================");
    $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
    return $DB->LastID();
  else:
    echo 'DB write problem: ' . $drive_id . ' ' . $quote_id . ' ' . $contact_id . ' ' . $email;
  endif;
}

writeLog("=================== START AJAX ===================");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("crm");

$temp_id = $_REQUEST["tempid"];
$temp_name = $_REQUEST["tempname"];
$request = $_REQUEST["request"];
$elementid = $_REQUEST["elementid"];
$companyid = $_REQUEST["companyid"];
$pagemode = $_REQUEST["pagemode"];
$commonFolderId = $_REQUEST["commonfolderid"];
$targetFolderId = $_REQUEST["targetfolderid"];
$file_format = $_REQUEST["fileformat"];
$quoteidval = $_REQUEST["quoteidval"];

$redirect = $_REQUEST["redirect"] == 'y';

$storeDirPath = $_SERVER["DOCUMENT_ROOT"]. "/upload/filledtemplates/";
$userId = $USER->GetID();

$driveFileName = '';

global $currentContactID,$currentCompanyID,$currentQuoteID, $curContactEmail;

writeLog("=================== CONTACT ID " . $elementid);
writeLog("=================== COMPANY ID " . $companyid);

function getCompaniesByContactId($cid){
  global $DB;
  $ComByCon = [];
  $strSql = "SELECT COMPANY_ID FROM b_crm_contact_company WHERE CONTACT_ID = '".strval($cid)."'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  while ($record = $recordset->fetch())
  {
    $ComByCon[] = $record['COMPANY_ID'];
  }
  return $ComByCon;
}

function getQuotesByContactId($cid){
  global $DB;
  $QuoteByCon = [];
  $strSql = "SELECT QUOTE_ID FROM b_crm_quote_contact WHERE CONTACT_ID = '".strval($cid)."'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  while ($record = $recordset->fetch())
  {
    $QuoteByCon[] = $record['QUOTE_ID'];
  }
  return $QuoteByCon;
}

function getContactsByQuoteId($qid){
  global $DB;
  $ConByQuote = [];
  $strSql = "SELECT CONTACT_ID FROM b_crm_quote_contact WHERE QUOTE_ID = '".strval($qid)."'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  while ($record = $recordset->fetch())
  {
    $ConByQuote[] = $record['CONTACT_ID'];
  }
  return $ConByQuote;
}

function getContactName($id) {

  global $DB;
  $name = '';
  $strSql = "SELECT FULL_NAME FROM b_crm_contact WHERE ID = '".strval($id)."'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  if ($record = $recordset->fetch())
  {
    $name = $record['FULL_NAME'];
  }

  return $name;
}

function getQuoteName($id) {

  global $DB;
  $name = '';
  $strSql = "SELECT TITLE FROM b_crm_quote WHERE ID = '".strval($id)."'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  if ($record = $recordset->fetch())
  {
    $name = $record['TITLE'];
  }

  return $name;
}

function safeStr($str){
  //filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
  return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), strip_tags($str));
}

/*
	@3 = person / contact person
	@4 = company
*/
function getCustomAddress($id, $type_id){
	global $DB;
  
  $reqStrSql = "SELECT RQ_CONTACT, RQ_EMAIL, RQ_PHONE, RQ_FAX FROM b_crm_requisite WHERE ENTITY_ID = '".$id."'";
  $req_recordset = $DB->Query($reqStrSql, false, $err_mess.__LINE__);
  while ($requisite = $req_recordset->Fetch())
  {
  	foreach($requisite as $key => $req):
  		$key = str_replace("RQ", "ADDRESS", $key);
    	$address[$key] = $req;
    endforeach;
  }
  
  $strSql = "SELECT * FROM b_crm_addr WHERE ANCHOR_TYPE_ID = '".$type_id."' AND ANCHOR_ID = '".$id."' AND ENTITY_TYPE_ID = '8'";
  $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
  while ($record = $recordset->fetch())
  {
    $address["LIST"][] = $record;
  }

  return $address;
}

function UFtoString($str, $arr, $data){

  $res = '*unknown*';

  if($arr['TYPE'] == 'boolean'):
    $res = '*bool*';
    if ($data == '1') $res = 'Yes'; else $res = 'No';
  endif;//if boolean

  if($arr['TYPE'] == 'enumeration'):
    $res = '*enum*';
    $arRes = [];
    $rsEnum = CUserFieldEnum::GetList(array(), array("ID" => $data));
    while ($arCat = $rsEnum->GetNext()){
      $arRes[] = $arCat["VALUE"];//first
    }
    $res = join(', ', $arRes);
  endif;//if enumeration

  return safeStr($res);
}

function getField($ENTITY_ID, $TYPE_ID, $ELEMENT_ID, $VALUE_TYPE){
	$arField = CCrmFieldMulti::GetList(array('ID' => 'asc'),	array('ENTITY_ID' => $ENTITY_ID, 'TYPE_ID' => $TYPE_ID, 'ELEMENT_ID' => $ELEMENT_ID, 'VALUE_TYPE'=>$VALUE_TYPE))->Fetch();
	
	return $arField["VALUE"];
}



function arInData(){

  global $currentContactID,$currentCompanyID,$currentQuoteID,$curContactEmail;
  $quoteidval = $_REQUEST['quoteidval'];
  $arData = [];
  $UF=[];
  $pagemode = $GLOBALS['pagemode'];

  writeLog("========================== PAGEMODE " . $pagemode);

  $dateTimeVal = date('dmyHi');
  $newFilename = '';
  if($pagemode === 'contact') {
    writeLog("= C =");
    $currentContactID = $GLOBALS['elementid'];
    $newFilename = getContactName(intval($currentContactID)) . " - " . $dateTimeVal;
    $currentCompanyID = getCompaniesByContactId($currentContactID)[0];
    $currentQuoteID = (intval($quoteidval) > 0) ? $quoteidval : $currentContactID;
  }
  if($pagemode === 'quote') {
    writeLog("= D =");
    $currentQuoteID = $GLOBALS['elementid'];
    $newFilename = getQuoteName(intval($currentQuoteID)) . " - " . $dateTimeVal;
    $currentContactID = getContactsByQuoteId($currentQuoteID)[0];
    if (isset($GLOBALS['companyid']) && $GLOBALS['companyid']!==''){
      $currentCompanyID = $GLOBALS['companyid'];
    }else{
      $currentCompanyID = getCompaniesByContactId($currentContactID)[0];
    }
  }

  writeLog("========================== GLOBALS['temp_name'] " . $GLOBALS['temp_name']);

  $GLOBALS['driveFileName'] = $GLOBALS['temp_name'] . '-' . $currentQuoteID . '-' . $dateTimeVal;
  $GLOBALS['newFileName'] = $newFilename;

  writeLog("= CONTACT ID    " . $currentContactID);
  writeLog("= COMPANY ID    " . $currentCompanyID);
  writeLog("= QUOTE ID      " . $currentQuoteID);

  $curContactEmail = 'unknown@email.field';//  - if many - which one? (Mike) //$dbCCrmContactGetByID['EMAIL'];

  ////////////////////////////////////////////////////////////////////////////////////
  // DUMB BITRIX FIXES
  ////////////////////////////////////////////////////////////////////////////////////

  $resEx = CCrmContact::GetListEx(array("ID"=>"ASC") ,array("ID" => $currentContactID), false, false, array("HONORIFIC"));
  while($arFieldsEx = $resEx->GetNext()){
    $d = \CCrmStatus::GetStatusList('HONORIFIC');
    $arData['CONTACT_HONORIFIC'] = $d[strval($arFieldsEx['HONORIFIC'])];
  }

  //In case of Complex UserField - Pass t to parsing function
  $resCCrmContactGetList = CCrmContact::GetList(array(),array("ID" => $currentContactID));
  if($arFields = $resCCrmContactGetList->Fetch())
  {
    foreach($arFields as $key => $value):
      if(strpos($key, 'UF_CRM_') === 0){
        if($UF[$key]['TYPE'] === 'string'){
          $arData[$UF[$key]['NAME']] = $value;
        } else {
          $arData[$UF[$key]['NAME']] = UFtoString($key, $UF[$key], $arFields[$key]);
        }
      }
    endforeach;
  }

  ////////////////////////////////////////////////////////////////////////////////////

  $dbCCrmQuoteGetByID = CCrmQuote::GetByID($currentQuoteID);

  //Passing Simple String Data for Fields to Values
  foreach ($dbCCrmQuoteGetByID as $key => $value) {
    $arData['QUO_'.strtoupper($key)] = safeStr($value);
  }

  //In case of Complex UserField - Pass t to parsing function
  $resCCrmQuoteGetList = CCrmQuote::GetList(array(),array("ID" => $currentQuoteID));
  if($arFields = $resCCrmQuoteGetList->Fetch())
  {
    foreach($arFields as $key => $value):
      if(strpos($key, 'UF_CRM_') === 0){
        if($UF[$key]['TYPE'] === 'string'){
          $arData[$UF[$key]['NAME']] = $value;
        } else {
          $arData[$UF[$key]['NAME']] = UFtoString($key, $UF[$key], $arFields[$key]);
        }
      }
    endforeach;
  }
  
  /* About seller / address */
  $seller_company_id = $arData["QUO_MYCOMPANY_ID"];
  $dbCCrmCompanyGetByID = CCrmCompany::GetByID($seller_company_id, false);

  //Passing Simple String Data for Fields to Values
  foreach ($dbCCrmCompanyGetByID as $key => $value) {
    $arData['SLR_'.strtoupper($key)] = safeStr($value);
  }
  
  //In case of Complex UserField - Pass t to parsing function
  $resCCrmCompanyGetList = CCrmCompany::GetList(array(),array("ID" => $seller_company_id));
  if($arFields = $resCCrmCompanyGetList->Fetch())
  {
    foreach($arFields as $key => $value):
      if(strpos($key, 'UF_CRM_') === 0){
        if($UF[$key]['TYPE'] === 'string'){
          $arData[$UF[$key]['NAME']] = $value;
        } else {
          $arData[$UF[$key]['NAME']] = UFtoString($key, $UF[$key], $arFields[$key]);
        }
      }
    endforeach;
  }
  
  /* Seller address */
  $seller_id = $arData["QUO_MYCOMPANY_ID"];
  $seller_address_res = getCustomAddress($seller_id, 4);
  foreach($seller_address_res as $key => $address):
  	switch($key):
  		case 'ADDRESS_1':
  			$key = 'ADDRESS';
	  		break;
	  		
	  	default:
	  		$key = "ADDRESS_".$key;
	  		break;
  	endswitch;
  	
  	$arData['SLR_'.strtoupper($key)] = safeStr($address);
  endforeach;
  
  $ENTITY_ID = "COMPANY";
  $arData['SLR_PHONE'] = getField($ENTITY_ID, "PHONE", $seller_id);
  $arData['SLR_FAX'] = getField($ENTITY_ID, "PHONE", $seller_id, "FAX");
  $arData['SLR_WEB'] = getField($ENTITY_ID, "WEB", $seller_id);
  $arData['SLR_EMAIL'] = getField($ENTITY_ID, "EMAIL", $seller_id);
  
  /* Client Info */
  if($arData["QUO_COMPANY_ID"]>0)
  {
		$client_id = $arData["QUO_COMPANY_ID"];
		$dbCCrmGetClientByID = CCrmCompany::GetByID($client_id, false);
		$client_type = 4; //company
		$ENTITY_ID = "COMPANY";
  }
  else
  {
  	$client_id = $arData["QUO_CONTACT_ID"];
		$dbCCrmGetClientByID = CCrmContact::GetByID($client_id, false);
		$client_type = 3; //contact
		$ENTITY_ID = "CONTACT";
  }

  //Passing Simple String Data for Fields to Values
  foreach ($dbCCrmGetClientByID as $key => $value) {
    $arData['CLI_'.strtoupper($key)] = safeStr($value);
  }
  
  $arData['CLI_PHONE'] = getField($ENTITY_ID, "PHONE", $client_id);
  $arData['CLI_FAX'] = getField($ENTITY_ID, "PHONE", $client_id, "FAX");
  $arData['CLI_WEB'] = getField($ENTITY_ID, "WEB", $client_id);
  $arData['CLI_EMAIL'] = getField($ENTITY_ID, "EMAIL", $client_id);
  
  /* Client address */
  $client_address_res = getCustomAddress($client_id, $client_type);
  foreach($client_address_res as $key => $address):
  	if($key == "LIST")
  	{
			foreach($address as $addrKey => $addr){
				foreach($addr as $addr_key => $addr_val):
					$addr_key = ($addr_key == 'ADDRESS_1') ? "ADDRESS" : "ADDRESS_".$addr_key;
					
					if($addrKey == 1)
					{
						$arData['CLI_'.$addr_key] = $addr_val;
					}
					else
					{
						$arData['CLI_DELIVERY_'.$addr_key] = $addr_val;
					}
				endforeach;
			}
		}
		else
		{
			$arData['CLI_'.strtoupper($key)] = safeStr($address);
		}
  endforeach;
  
  ////////////////////////////////////////////////////////////////////////////////////

    $arData['HLP_CURRENTDATE'] = safeStr(date('d-m-Y'));
    $arData['QUO_QUOTEIDCODE'] = explode('-', $GLOBALS['driveFileName'])[1] . '-' . explode('-', $GLOBALS['driveFileName'])[2];

  ////////////////////////////////////////////////////////////////////////////////////
  
  $product = CCrmQuote::LoadProductRows($currentQuoteID);
  
  $arPrice = array();
  $arDiscount = array();
  //foreach($products as $key => $product):
  for($key = 0; $key < 10; $key++){
  	if($product[$key]){
			foreach($product[$key] as $product_key => $prod_value)
			{
				if($product_key == "DISCOUNT_SUM")
				{
					$discount = $prod_value * $product[$key]["QUANTITY"];
					$arData["PROD_".$product_key."_".$key] = $discount;
					$arDiscount[] = $discount;
				}
				else
				{
					$arData["PROD_".$product_key."_".$key] = $prod_value;
				}
			}
		}
		else
		{
			$arData["PROD_PRODUCT_NAME_".$key] = "";
			$arData["PROD_QUANTITY_".$key] = "";
			$arData["PROD_PRICE_NETTO_".$key] = "";
			$arData["PROD_DISCOUNT_SUM_".$key] = "";
			$arData["PROD_PRICE_".$key] = "";
		}
  	
  	$price = $product[$key]["PRICE"] * $product[$key]["QUANTITY"];
  	$arData["PROD_ROW_TOTAL_".$key] = $price;
  	$arPrice[] = $price;
  	
  	$arData["PROD_NOTE_".$key] = "";
  }
  
  $arData["PROD_DISCOUNT_TOTAL"] = array_sum($arDiscount);
  $arData["PROD_TOTAL"] = array_sum($arPrice);
  
  return $arData;
}

if(isset($_GET["debug"])){

/**/
echo "<pre>";
print_r(arInData());
echo "</pre>";
die("xxx");
/**/

}

  //Check if the directory already exists.
  if(!is_dir($storeDirPath)){
    //Directory does not exist, so lets create it.
    mkdir($directoryName, 0755, true);
  }

  if ( ($request == "render") && ($temp_id != "") ) {

      writeLog("=================== AJAX: GET TEMPLATE ===================");
      writeLog("=================== IN ID: ".$temp_id);
      
      $f = \CFile::getFileArray($temp_id);
      $path = $_SERVER["DOCUMENT_ROOT"]."/crm/quote/QuoteTemplate3.docx"; //$_SERVER["DOCUMENT_ROOT"].$f['SRC'];
      $data = file_get_contents($path);
      
      writeLog("=================== AJAX: FILE PATH: " . $path);
      //writeLog("=================== AJAX: FILE base64_encode DATA: " . base64_encode($data));


      writeLog("=================== AJAX: SENDING TO MAKE SERVER ===================");

      writeLog("=================== AJAX: START PREPARE DATA =======================");

      $uploadFile = $path;

      // Prepare remote upload data
      $uploadRequest = array(
        'fileName' => basename($uploadFile),
        'fileData' => base64_encode(file_get_contents($uploadFile)),
        'templateData' => json_encode(arInData())
      );

      writeLog("=================== AJAX: END PREPARE DATA ========================");

      //writeLog("=================== AJAX: SENDING DATA: " . serialize($uploadRequest));

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://verifications.ideasfornow.nl/converter/docxTemplate2docx.php");
      // curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadRequest);

      $result = curl_exec($ch);
      curl_close($ch);

    	writeLog("=================== AJAX: RECIEVING FROM MAKE SERVER ===================");

        $fileName = basename($uploadFile .'.docx');
        $fileDiskPath = $storeDirPath . $fileName;
        file_put_contents($fileDiskPath, $result);

    	writeLog("=================== AJAX: SAVE TO DRIVE===================");

      if (file_exists($fileDiskPath)) {

        //$file_format = 'docx';

        if(($file_format === 'docx') || ($file_format === 'pdf') ):

          $fileArray = \CFile::MakeFileArray($fileDiskPath);

          $fileId = \CFile::SaveFile($fileArray, 'filledtemplates'); ///home/bitrix/www/upload

          $commonId = 'shared_files_s1'; //b_disk_storage//ENTITY_ID <- Company Drive Storage
          $storage = \Bitrix\Disk\Driver::getInstance()->getStorageByCommonId($commonId);
          $root = $storage->getRootObject();
          $folder = $root->loadById($targetFolderId);
          $securityContext = $storage->getFakeSecurityContext();

          writeLog("=================== FILE FOLDER = " . $folder->getName());

          $quote_id = $GLOBALS['driveFileName'];

          writeLog("=================== QUOTEID FOLDER = " . $quote_id);

          writeLog('#--------------------------------------- QUOTEID');

          $quote_id_folder = explode('-', $quote_id)[1];

          //trying to create quoteID folder
          if(!is_null($subFolderModelCode = $folder->addSubFolder(array('NAME' => $quote_id_folder, 'CREATED_BY' => $userId)))){
          // if QUOTEID folder just created
          }else{
          // if QUOTEID folder exists
            $childrenFilesOrFolders = $folder->getChildren($securityContext);
            foreach ($childrenFilesOrFolders as $fileOrFolder)
            {
              if ($fileOrFolder->getName() === $quote_id_folder)
              {
                $existingFolderId = $fileOrFolder->getId();
                $subFolderModelCode = $fileOrFolder;
              }
            }
          }
          writeLog('$subFolderModelCode:'.$subFolderModelCode->getName());

          $newFilename = $GLOBALS['newFileName'];
          $tmpName = $GLOBALS['temp_name'];
          preg_match_all('/\(([A-Za-z0-9 ]+?)\)/', $tmpName, $arTmpName);
          
          $tmpNameVal = current($arTmpName);
          if($tmpNameVal[0] != '')
            $newFilename = $tmpNameVal[0] ." ". $newFilename;

          $fileModel = $subFolderModelCode->addFile(array(
            'NAME' => "{$newFilename}.docx",//"{$quote_id}.docx",
            'FILE_ID' => $fileId, //b_file
            'SIZE' => $fileArray['size'], //denormalization
            'CREATED_BY' => $userId
          ));

          if($file_format === 'docx') unlink($fileDiskPath);

        endif; //if file format === docx

        if($file_format === 'pdf'):

          //# REMOTE PDF CONVERSION PART --------------------------------------------------
          writeLog("=================== START REMOTE PDF CONVERSION PART = ");
          $uploadFile = $fileDiskPath;//'/home/bitrix/www/crm/contact/demo.docx';

          // Prepare remote upload data
          $uploadRequest = array(
            'fileName' => basename($uploadFile),
            'fileData' => base64_encode(file_get_contents($uploadFile))
          );

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "https://verifications.ideasfornow.nl/converter/doc2pdf.php");
          // curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_TIMEOUT, 30);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);

          curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadRequest);

          $result = curl_exec($ch);
          curl_close($ch);
          #echo '<pre>$result is ';
          #print_r($result);
          #echo '</pre>';
          #exit();

          file_put_contents($fileDiskPath.'.pdf', $result);
          writeLog("=================== END REMOTE PDF CONVERSION PART = ");


          $fileArray = \CFile::MakeFileArray($fileDiskPath.'.pdf');

          $fileId = \CFile::SaveFile($fileArray, 'filledtemplates');

          $folder = $root->loadById($targetFolderId);

          writeLog("=================== PDF FILE FOLDER = " . $folder->getName());

          $quote_id = $GLOBALS['driveFileName'];

          $quote_id_folder = explode('-', $quote_id)[1];

          $securityContext = $storage->getFakeSecurityContext();
          //trying to create quoteID folder
          if(!is_null($subFolderModelCode = $folder->addSubFolder(array('NAME' => $quote_id_folder, 'CREATED_BY' => $userId)))){
          // if quoteID folder just created
          }else{
          // if quoteID folder exists
            $childrenFilesOrFolders = $folder->getChildren($securityContext);
            foreach ($childrenFilesOrFolders as $fileOrFolder)
            {
              if ($fileOrFolder->getName() === $quote_id_folder)
              {
                $existingFolderId = $fileOrFolder->getId();
                $subFolderModelCode = $fileOrFolder;
              }
            }
          }
          writeLog('$subFolderModelCode:'.$subFolderModelCode->getName());

          $fileModel = $subFolderModelCode->addFile(array(
            'NAME' => "{$quote_id}.pdf",
            'FILE_ID' => $fileId, //b_file
            'SIZE' => $fileArray['size'], //denormalization
            'CREATED_BY' => $userId
          ));

          unlink($fileDiskPath.'.pdf');

          /// REMOTE PDF CONVERSION PART --------------------------------------------------

        endif;//if file format ==== pdf

      } //if (file_exists($fileDiskPath))

      if($fileModel->getID() > 0):
        writeLog("=================== DB START ====: ".$currentQuoteID."============================ ");
        ///////////////////////////////////////////////////////////
        writeLog("========================== EMAIL " . $curContactEmail);

        $ctblquoteID = 0;
        $ctblcontactID = 0;
        if($pagemode == 'contact') {
          $ctblcontactID = strval($currentContactID);
          $ctblquoteID = (intval($quoteidval) > 0) ? $quoteidval : 0;
        }
        else if($pagemode == 'quote') {
          $ctblquoteID = $currentQuoteID;
        }

        writeToDB(strval($fileModel->getID()), strval($ctblquoteID), strval($ctblcontactID), $curContactEmail);
        //////////////////////////////////////////////////////////
        writeLog("=================== DB END ================================ ");
      endif;

      $newFilename = ($newFilename != '') ? $newFilename : $quote_id;
     
      if($redirect) {
        LocalRedirect('/docs/shared/file/'.$folder->getName() . '/' . $subFolderModelCode->getName() . '/' . "{$newFilename}.{$file_format}");
      }
      else echo urldecode($folder->getName() . '/' . $subFolderModelCode->getName() . '/' . "{$newFilename}.{$file_format}");

  } else {

    writeLog("=================== AJAX: NO REQUEST MODE OR NO DATA ===========");

  } // if ( ($request == "render") && ($temp_id != "") )

  ///////////////////////////////////////////////////////////////////////////////////////////////

writeLog("=================== END AJAX ===================");
?>
