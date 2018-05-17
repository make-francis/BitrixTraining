<?php
function writeLog($message) {
    $handle = fopen($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/debug.log', 'a');
    $line   = '[' . date('Y-m-d H:i:s T') . '] ' . $message . "\n";
    fwrite($handle, $line);
    fclose($handle);
}


function writeToDB($drive_id, $invoice_id, $contact_id, $email){
    if ($drive_id > 0 && strlen($email) > 0) :
        global $DB;
        $strSql = "INSERT INTO b_disk_crm_template (DRIVE_ID, INVOICE_ID, CONTACT_ID, EMAIL) VALUES (".$drive_id.", ".$invoice_id.", ".$contact_id.", '".$email."')";
        writeLog("=================== QUERY ===================");
        $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
        return $DB->LastID();
    else:
        echo 'DB write problem: ' . $drive_id . ' ' . $invoice_id . ' ' . $contact_id . ' ' . $email;
    endif;
}

function getCompaniesByContactId($cid)
{
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

function getInvoiceByInvoiceId($cid)
{
    global $DB;
    $InvoiceByCon = [];
    $strSql = "SELECT INVOICE_ID FROM b_crm_invoice_contact WHERE CONTACT_ID = '".strval($cid)."'";
    $recordset = $DB->Query($strSql, false, $err_mess.__LINE__);
    while ($record = $recordset->fetch())
    {
        $QuoteByCon[] = $record['INVOICE_ID'];
    }
    return $QuoteByCon;
}

function getContactsByInvoiceId($qid)
{
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

function getContactName($id)
{
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

function getInvoiceName($id)
{
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

function safeStr($str)
{
    //filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    return str_replace(
            array('&', '<', '>', '\'', '"'), 
            array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), 
            strip_tags($str)
        );
}

/*
    @3 = person / contact person
    @4 = company
*/
function getCustomAddress($id, $type_id)
{
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

function UFtoString($str, $arr, $data)
{
    $res = '*unknown*';

    if($arr['TYPE'] == 'boolean') {
        $res = '*bool*';
        if ($data == '1')
            $res = 'Yes';
        else
            $res = 'No';
    } //if boolean

    if($arr['TYPE'] == 'enumeration') {
        $res = '*enum*';
        $arRes = [];
        $rsEnum = CUserFieldEnum::GetList(array(), array("ID" => $data));
        while ($arCat = $rsEnum->GetNext()){
            $arRes[] = $arCat["VALUE"];//first
        }
        $res = join(', ', $arRes);
    } //if enumeration

    return safeStr($res);
}

function getField($ENTITY_ID, $TYPE_ID, $ELEMENT_ID, $VALUE_TYPE)
{
    CModule::IncludeModule("crm") or exit('Failed including required module(s).');

    $result = CCrmFieldMulti::GetList(
            array('ID' => 'asc'),
            array(
                    'ENTITY_ID' => $ENTITY_ID, 
                    'TYPE_ID' => $TYPE_ID, 
                    'ELEMENT_ID' => $ELEMENT_ID, 
                    'VALUE_TYPE'=>$VALUE_TYPE
                )
        );
    $value = null;
    if ($arField = $result->Fetch()) {
        $value = trim($arField["VALUE"]);
    }
    
    return $value;
}

function getManagedAddress()
{
    global $DB;

    // fetch the assignment set by the address manager
    $query = 'SELECT * FROM m_field_translations WHERE type="address"';
    $result = $DB->Query($query);
    $assignments = array();
    while ($row = $result->Fetch()) {
        $assignments[$row['field_code']] = intval($row['prop_id']);
    }

    return $assignments;
}

function arInData()
{
    global $currentContactID,$currentCompanyID,$currentInvoiceID,$curContactEmail;
    // setlocale(LC_MONETARY, 'en_US'); // does not work due to missing locale on linux
    setlocale(LC_MONETARY, 'en_US.UTF-8');

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
        $currentInvoiceID = (intval($invoiceidval) > 0) ? $invoiceidval : $currentContactID;
    }
    if($pagemode === 'invoice') {
        writeLog("= D =");
        $currentInvoiceID = $GLOBALS['elementid'];
        $newFilename = getInvoiceName(intval($currentInvoiceID)) . " - " . $dateTimeVal;
        $currentContactID = getContactsByInvoiceId($currentInvoiceID)[0];
        if (isset($GLOBALS['companyid']) && $GLOBALS['companyid']!==''){
            $currentCompanyID = $GLOBALS['companyid'];
        }else{
            $currentCompanyID = getCompaniesByContactId($currentContactID)[0];
        }
    }

    writeLog("========================== GLOBALS['temp_name'] " . $GLOBALS['temp_name']);

    $GLOBALS['driveFileName'] = $GLOBALS['temp_name'] . '-' . $currentInvoiceID . '-' . $dateTimeVal;
    $GLOBALS['newFileName'] = $newFilename;

    writeLog("= CONTACT ID    " . $currentContactID);
    writeLog("= COMPANY ID    " . $currentCompanyID);
    writeLog("= INVOICE ID      " . $currentInvoiceID);

    $curContactEmail = 'unknown@email.field';//  - if many - which one? (Mike) //$dbCCrmContactGetByID['EMAIL'];

    ////////////////////////////////////////////////////////////////////////////////////
    // DUMB BITRIX FIXES
    ////////////////////////////////////////////////////////////////////////////////////

    CModule::IncludeModule("crm") or exit('Failed including required module(s).');

    $resEx = CCrmContact::GetListEx(
            array("ID"=>"ASC"),
            array("ID" => $currentContactID),
            false,
            false,
            array("HONORIFIC")
        );
    while($arFieldsEx = $resEx->GetNext()) {
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

    $dbCCrmInvoiceGetByID = CCrmInvoice::GetByID($currentInvoiceID);
    // if (isset($_REQUEST['debug'])) {
    //     echo '<pre>$dbCCrmInvoiceGetByID is ';
    //     print_r($dbCCrmInvoiceGetByID);
    //     echo '</pre>';
    // }

    //Passing Simple String Data for Fields to Values
    foreach ($dbCCrmInvoiceGetByID as $key => $value) {
        $arData['INV_'.strtoupper($key)] = safeStr($value);
    }

    //In case of Complex UserField - Pass t to parsing function
    $resCCrmInvoiceGetList = CCrmInvoice::GetList(array(),array("ID" => $currentInvoiceID));

    if($arFields = $resCCrmInvoiceGetList->Fetch())
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
    $seller_company_id = $arData["INV_UF_MYCOMPANY_ID"];
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
    $seller_id = $arData["INV_UF_MYCOMPANY_ID"];
    $seller_address_res = getCustomAddress($seller_id, 4);
    foreach($seller_address_res as $key => $address):
    if($key == "LIST")
    {
        foreach($address as $addr){
            foreach($addr as $addr_key => $addr_val):
                $addr_key = ($addr_key == 'ADDRESS_1') ? "ADDRESS" : "ADDRESS_".$addr_key;
                $arData['SLR_'.$addr_key] = $addr_val;
            endforeach;
        }
    }
    else
    {
        $arData['SLR_'.strtoupper($key)] = safeStr($address);
    }
    endforeach;

    $ENTITY_ID = "COMPANY";
    $arData['SLR_PHONE'] = getField($ENTITY_ID, "PHONE", $seller_id);
    $arData['SLR_FAX'] = getField($ENTITY_ID, "PHONE", $seller_id, "FAX");
    $arData['SLR_WEB'] = getField($ENTITY_ID, "WEB", $seller_id);
    $arData['SLR_EMAIL'] = getField($ENTITY_ID, "EMAIL", $seller_id);

    /* Client Info */
    if($arData["INV_UF_COMPANY_ID"]>0)
    {
        $client_id = $arData["INV_UF_COMPANY_ID"];
        $dbCCrmGetClientByID = CCrmCompany::GetByID($client_id, false);
        $client_type = 4; //company
        $ENTITY_ID = "COMPANY";
    }
    else
    {
        $client_id = $arData["INV_UF_COMPANY_ID"];
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
            $addressKey = getManagedAddress();        
            $tplAddress = array();
            foreach($address as $tmpkey => $addressRow){

                if ($addressRow['TYPE_ID'] == $addressKey['billing_address']) {
                    $tplAddress['billing_address'] = $addressRow;
                } elseif ($addressRow['TYPE_ID'] == $addressKey['delivery_address']) {
                    $tplAddress['delivery_address'] = $addressRow;
                }
            }

            foreach ($tplAddress as $key => $addressRow) {

                $prefix = ('billing_address'==$key)?'CLI_':'CLI_DELIVERY_';

                foreach ($addressRow as $key => $value) {

                    $addr_key = (in_array($key, array('ADDRESS_1', 'ADDRESS_2')))?"ADDRESS":"ADDRESS_".$key;

                    if ('ADDRESS' == $addr_key) {
                        if (!isset($arData[$prefix.$addr_key])) {
                            $arData[$prefix.$addr_key] = '';
                        }
                        $arData[$prefix.$addr_key] .= $value. ' ';
                    } else {
                        $arData[$prefix.$addr_key] = $value;
                    }
                }
            }
        }
        else
        {
            $arData['CLI_'.strtoupper($key)] = safeStr($address);
        }
    endforeach;

    $arData['HLP_CURRENTDATE'] = safeStr(date('d-m-Y'));
    $arData['INV_INVOICEIDCODE'] = explode('-', $GLOBALS['driveFileName'])[1] . '-' . explode('-', $GLOBALS['driveFileName'])[2];

    $product = CCrmInvoice::GetProductRows($currentInvoiceID);
    $arPrice = array();
    $arDiscount = array();

    global $DB;

    $totalNoDiscounts = 0.00;
    $prodTotal = 0.00;

    //foreach($products as $key => $product):
    for($key = 0; $key < 18; $key++){
        $productRow = $product[$key];
        $product_row_id = $productRow["ID"];
        // if (isset($_REQUEST['debug'])) {
        //     echo '<pre>$productRow is ';
        //     print_r($productRow);
        //     echo '</pre>';
        // }
        

        $sql = "SELECT NOTES FROM b_sale_basket WHERE ORDER_ID = '".$productRow["ORDER_ID"]."' AND PRODUCT_ID = '".$productRow["PRODUCT_ID"]."'";
        $recordset = $DB->Query($sql, false, $err_mess.__LINE__);
        while ($record = $recordset->fetch())
        {
            $product_notes = $record["NOTES"];
        }

        if($productRow){
            foreach($productRow as $product_key => $prod_value)
            {
                switch($product_key){
                    case "DISCOUNT_PRICE":
                    $discount = $productRow["PRICE"]; //bcmul($productRow["DISCOUNT_PRICE"], $productRow["QUANTITY"], 4);
                    $arData["PROD_".$product_key."_AMOUNT_".$key] = money_format('%!i', $discount);
                    $arData["PROD_".$product_key."_".$key] = money_format('%!i', $prod_value);
                    $arDiscount[] = $discount;
                    break;

                    case "NOTES":
                    $arData["PROD_NOTE_".$key] = $product_notes;
                    break;

                    case "PRICE":
                    $price = bcadd($productRow["PRICE"], $productRow["DISCOUNT_PRICE"], 4);
                    $price = money_format('%!i', $price);
                    $arData["PROD_".$product_key."_".$key] = $price;

                    if ($productRow['DISCOUNT_PRICE']>0) {
                        $totalNoDiscounts += (($productRow["PRICE"]+$productRow['DISCOUNT_PRICE'])*$productRow["QUANTITY"]);
                    } else {
                        $totalNoDiscounts += ($productRow["PRICE"]*$productRow["QUANTITY"]);
                    }
                    
                    break;

                    default:
                    if (stripos($product_key, 'quantity')!==false) {
                        $prod_value = intval($prod_value);
                    }
                    $arData["PROD_".$product_key."_".$key] = $prod_value;
                    break;
                }
            }
        }
        else
        {
            $arData["PROD_PRODUCT_NAME_".$key] = "";
            $arData["PROD_QUANTITY_".$key] = "";
            $arData["PROD_DISCOUNT_PRICE_".$key] = "";
            $arData["PROD_DISCOUNT_PRICE_AMOUNT_".$key] = "";
            $arData["PROD_PRICE_".$key] = "";
            $arData["PROD_NOTE_".$key] = "";
        }

        $price = '';
        if (isset($product[$key]["PRICE"]) && isset($product[$key]["QUANTITY"])) {
            $price = $product[$key]["PRICE"] * $product[$key]["QUANTITY"];
            $price = money_format('%!i', $price);
        }
        
        $arPrice[] = $price; // $arPrice now contains an array of strings instead of numbers
        $prodTotal += ($product[$key]["PRICE"] * $product[$key]["QUANTITY"]);
        $arData["PROD_ROW_TOTAL_".$key] = ($price>0)?$price:'';

        $arData["PROD_NOTE_".$key] = "";
    }

    $arData["PROD_NO_DISCOUNT_TOTAL"] = money_format('%!i', $totalNoDiscounts);
    $arData["PROD_DISCOUNT_TOTAL"] = money_format('%!i', array_sum($arDiscount));
    // $arData["PROD_TOTAL"] = money_format('%!i', array_sum($arPrice));
    $arData["PROD_TOTAL"] = money_format('%!i', $prodTotal);
    $arData["TAX_TOTAL"] = money_format('%!i', 0.00);

    $lang = (isset($_REQUEST['lang']))?strtolower($_REQUEST['lang']):'en';
    $translations = getTranslations('invoice', $lang);
    $arData = array_merge($arData, $translations);

    /*if (isset($_REQUEST['debug'])) {
        var_dump($prodTotal);
        echo '<pre>$arPrice is ';
        print_r($arPrice);
        echo '</pre>';
        echo '<pre>';
        print_r($arData);
        echo '</pre>';
        exit();
    }*/

    return $arData;
}

function getTranslations($type = 'invoice', $lang = 'en')
{
    $type = trim($type);
    $lang = trim($lang);
    global $DB;
    $query = 'SELECT * FROM m_report_translations WHERE type="'.$type.'" AND language="'.$lang.'"';
    $result = $DB->Query($query);

    $data = array();
    while ($row = $result->Fetch()) {
        extract($row);

        $data[ $code ] = $text;
    }
    
    return $data;
}

function convertDocRemote($params)
{
    define('DOC_CONVERTER_URL', "https://verifications.ideasfornow.nl/converter/docxTemplate2docx.php");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, DOC_CONVERTER_URL);
    // curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function convertPdfRemote($params)
{
    define('PDF_CONVERTER_URL', "https://verifications.ideasfornow.nl/converter/doc2pdf.php");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PDF_CONVERTER_URL);
    // curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function downloadFile($filepath, $type = 'pdf')
{
    // $ext = '.pdf';
    // if ('doc' ==  $type) {
    //     $ext = '.doc';
    // } elseif ('docx' ==  $type) {
    //     $ext = '.docx';
    // }
    $fileinfo = pathinfo($filepath);
    $sendname = $fileinfo['filename'] . '.' . strtoupper($fileinfo['extension']);

    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"$sendname\"");
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
}

function checkPath($path)
{
    // Check if the directory already exists.
    if(!is_dir($path)){
        
        // Directory does not exist, so lets create it.
        // mkdir($directoryName, 0755, true);
        mkdir($path, 0755, true);
    }

    return $path;
}
