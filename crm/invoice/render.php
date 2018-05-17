<?php
//invoice/ajax
error_reporting( E_ALL );
ini_set('display_errors', 1);

// @todo:
//$_SERVER["DOCUMENT_ROOT"].$f['SRC']; // get path from file template ID passed
if (strtolower($_REQUEST['lang']) == 'de')
    define('TEMPLATE_SOURCE', $_SERVER["DOCUMENT_ROOT"]."/crm/templates/Invoice-de.docx"); // path for source templates
else
    define('TEMPLATE_SOURCE', $_SERVER["DOCUMENT_ROOT"]."/crm/templates/Invoice.docx"); // path for source templates
define('TEMPLATE_DESTINATION', $_SERVER["DOCUMENT_ROOT"]. "/upload/filledtemplates/"); // path for converted templates

include 'functions.php';

writeLog("=================== START AJAX ===================");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("crm") or exit('Failed including required module(s).');
global $USER;
$userId = $USER->GetID();
if (!$userId) {
    exit('There was an error executing the script. Please contact the administrator.');
}

// prep request values
$templateId = $_REQUEST["tempid"];
$tempName = $_REQUEST["tempname"];
$request = $_REQUEST["request"];
$elementid = $_REQUEST["elementid"];
$companyid = $_REQUEST["companyid"];
$pagemode = $_REQUEST["pagemode"];
$commonFolderId = $_REQUEST["commonfolderid"];
$targetFolderId = $_REQUEST["targetfolderid"];
$file_format = $_REQUEST["fileformat"];
$file_format = trim($file_format);
$invoiceidval = $_REQUEST["invoiceidval"];
$redirect = $_REQUEST["redirect"] == 'y';

// $storeDirPath = $_SERVER["DOCUMENT_ROOT"]. "/upload/filledtemplates/";
$storeDirPath = TEMPLATE_DESTINATION;

$driveFileName = '';

global $currentContactID,$currentCompanyID,$currentInvoiceID, $curContactEmail;

writeLog("=================== CONTACT ID " . $elementid);
writeLog("=================== COMPANY ID " . $companyid);

// if(isset($_GET["debug"])){
//     /**/
//     echo "<pre>";
//     print_r(arInData());
//     echo "</pre>";
//     die("xxx");
//     /**/
// }

// verify the storage path
$storeDirPath = checkPath($storeDirPath);

if ( ($request == "render") && ($templateId != "") ) {
    writeLog("=================== AJAX: GET TEMPLATE ===================");
    writeLog("=================== IN ID: ".$templateId);

    // $f = \CFile::getFileArray($templateId);
    // $_SERVER["DOCUMENT_ROOT"].$f['SRC'];
    // $uploadFile = $_SERVER["DOCUMENT_ROOT"]."/crm/templates/InvoiceTemplate.docx"; 

    // convert the doc file first before rendering to pdf
    $uploadFile = TEMPLATE_SOURCE;
    // $data = file_get_contents($uploadFile);

    writeLog("=================== AJAX: FILE PATH: " . $uploadFile);
    //writeLog("=================== AJAX: FILE base64_encode DATA: " . base64_encode($data));
    writeLog("=================== AJAX: SENDING TO MAKE SERVER ===================");
    writeLog("=================== AJAX: START PREPARE DATA =======================");

    // Prepare remote upload data
    $uploadRequest = array(
            'fileName' => basename($uploadFile),
            'fileData' => base64_encode(file_get_contents($uploadFile)),
            'templateData' => json_encode(arInData())
        );

    writeLog("=================== AJAX: END PREPARE DATA ========================");

    //writeLog("=================== AJAX: SENDING DATA: " . serialize($uploadRequest));
    //

    $result = convertDocRemote($uploadRequest);

    writeLog("=================== AJAX: RECIEVING FROM MAKE SERVER ===================");

    // $uploadFile = str_ireplace(search, replace, subject)
    $fileInfo = pathinfo($uploadFile);

    // $fileName = basename($uploadFile .'.docx');
    $converted = str_ireplace(
            '.'.$fileInfo['extension'], 
            '-'.intval($elementid).'.'.$fileInfo['extension'], 
            $fileInfo['basename']
        );
    $fileDiskPath = $storeDirPath . $converted;
    file_put_contents($fileDiskPath, $result); // save converted template

    writeLog("=================== AJAX: SAVE TO DRIVE===================");

    if (file_exists($fileDiskPath)) {
        
        //$file_format = 'docx';
        if(in_array($file_format, array('docx', 'pdf'))):

            $fileArray = \CFile::MakeFileArray($fileDiskPath);
            $fileId = \CFile::SaveFile($fileArray, 'filledtemplates'); ///home/bitrix/www/upload

            $commonId = 'shared_files_s1'; //b_disk_storage//ENTITY_ID <- Company Drive Storage
            $storage = \Bitrix\Disk\Driver::getInstance()->getStorageByCommonId($commonId);
            $root = $storage->getRootObject();
            $folder = $root->loadById($targetFolderId);
            $securityContext = $storage->getFakeSecurityContext();

            writeLog("=================== FILE FOLDER = " . $folder->getName());

            $invoice_id = $GLOBALS['driveFileName'];

            writeLog("=================== INVOICEID FOLDER = " . $invoice_id);

            writeLog('#--------------------------------------- INVOICEID');

            $invoice_id_folder = explode('-', $invoice_id)[1];

            //trying to create invoiceID folder
            $subFolderModelCode = $folder->addSubFolder(
                    array('NAME' => $invoice_id_folder, 'CREATED_BY' => $userId)
                );

            if(!is_null($subFolderModelCode)){
                // if INVOICE_ID folder just created
            }else{
                // if INVOICE_ID folder exists
                $childrenFilesOrFolders = $folder->getChildren($securityContext);
                foreach ($childrenFilesOrFolders as $fileOrFolder)
                {
                    if ($fileOrFolder->getName() === $invoice_id_folder)
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
            if($tmpNameVal[0] != '') {
                $newFilename = $tmpNameVal[0] ." ". $newFilename;
            }

            $fileParams = array(
                    'NAME' => "{$newFilename}.docx",//"{$invoice_id}.docx",
                    'FILE_ID' => $fileId, //b_file
                    'SIZE' => $fileArray['size'], //denormalization
                    'CREATED_BY' => $userId
                );
            $fileModel = $subFolderModelCode->addFile($fileParams);            

            if('pdf' == $file_format):
                //# REMOTE PDF CONVERSION PART --------------------------------------------------
                writeLog("=================== START REMOTE PDF CONVERSION PART = ");
                $uploadFile = $fileDiskPath;//'/home/bitrix/www/crm/contact/demo.docx';

                // Prepare remote upload data
                $uploadRequest = array(
                        'fileName' => basename($uploadFile),
                        'fileData' => base64_encode(file_get_contents($uploadFile))
                    );

                $result = convertPdfRemote($uploadRequest);

                $converted = str_ireplace(
                        '.'.$fileInfo['extension'], 
                        '-'.intval($elementid).'.pdf', 
                        $fileInfo['basename']
                    );
                $pdfDiskPath = $storeDirPath . $converted;

                file_put_contents($pdfDiskPath, $result);
                
                writeLog("=================== END REMOTE PDF CONVERSION PART = ");

                $fileArray = \CFile::MakeFileArray($pdfDiskPath.'.pdf');

                $fileId = \CFile::SaveFile($fileArray, 'filledtemplates');

                $folder = $root->loadById($targetFolderId);

                writeLog("=================== PDF FILE FOLDER = " . $folder->getName());

                $invoice_id = $GLOBALS['driveFileName'];

                $invoice_id_folder = explode('-', $invoice_id)[1];

                $securityContext = $storage->getFakeSecurityContext();
                
                //trying to create invoiceID folder
                $subFolderModelCode = $folder->addSubFolder(
                        array(
                                'NAME' => $invoice_id_folder, 
                                'CREATED_BY' => $userId
                            )
                    );
                if(!is_null($subFolderModelCode)){
                    // if quoteID folder just created
                }else{
                    // if quoteID folder exists
                    $childrenFilesOrFolders = $folder->getChildren($securityContext);
                    foreach ($childrenFilesOrFolders as $fileOrFolder)
                    {
                        if ($fileOrFolder->getName() === $invoice_id_folder)
                        {
                            $existingFolderId = $fileOrFolder->getId();
                            $subFolderModelCode = $fileOrFolder;
                        }
                    }
                }
                writeLog('$subFolderModelCode:'.$subFolderModelCode->getName());

                $fileParams = array(
                        'NAME' => "{$invoice_id}.pdf",
                        'FILE_ID' => $fileId, //b_file
                        'SIZE' => $fileArray['size'], //denormalization
                        'CREATED_BY' => $userId
                    );
                $fileModel = $subFolderModelCode->addFile($fileParams);

                // force download
                downloadFile($pdfDiskPath, 'pdf');
                
                // delete tmp pdf after
                unlink($pdfDiskPath);

                /// REMOTE PDF CONVERSION PART --------------------------------------------------

            endif;//if file format ==== pdf

            // after processing the docx/pdf, delete converted template
            if('docx' == $file_format) {
                unlink($fileDiskPath);
            }

            writeLog("=================== END AJAX ===================");

            exit();

        endif; //if file format === docx

    } //if (file_exists($fileDiskPath))

    // if($fileModel->getID() > 0):
    //     writeLog("=================== DB START ====: ".$currentInvoiceID."============================ ");
    //     writeLog("========================== EMAIL " . $curContactEmail);

        // $ctblquoteID = 0;
        // $ctblcontactID = 0;
        // if($pagemode == 'contact') {
        //     $ctblcontactID = strval($currentContactID);
        //     $ctblquoteID = (intval($quoteidval) > 0) ? $quoteidval : 0;
        // } elseif($pagemode == 'quote') {
        //     $ctblquoteID = $currentInvoiceID;
        // }

        // writeToDB(strval($fileModel->getID()), strval($ctblquoteID), strval($ctblcontactID), $curContactEmail);
        
    //     writeLog("=================== DB END ================================ ");
    // endif;

    // $newFilename = ($newFilename != '') ? $newFilename : $invoice_id;

    // if($redirect) {
    //     $redirect = '/docs/shared/file/'.$folder->getName().'/'.$subFolderModelCode->getName().'/'."{$newFilename}.{$file_format}";
    //     LocalRedirect($redirect);
    // } else {
    //     echo urldecode($folder->getName().'/'.$subFolderModelCode->getName().'/'."{$newFilename}.{$file_format}");
    // }

} else {

    writeLog("=================== AJAX: NO REQUEST MODE OR NO DATA ===========");

} // if ( ($request == "render") && ($templateId != "") )

writeLog("=================== END AJAX ===================");

exit('Failed generating converted document. Please contact the administrator.');
