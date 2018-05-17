<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$find="function bcadd";

var_dump(class_exists('ReflectionFunction'));
$reflFunc = new ReflectionFunction('bcadd');
var_dump($reflFunc->getFileName() . ':' . $reflFunc->getStartLine());

var_dump(findString('./bitrix/',$find));

function findString($path,$find){
    $return='';
    ob_start();
    if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if(is_dir($path.'/'.$file)){
                    $sub=findString($path.'/'.$file,$find);
                    if(isset($sub)){
                        echo $sub.PHP_EOL;
                    }
                }else{
                    $ext=substr(strtolower($file),-3);
                    if($ext=='php'){
                        $filesource=file_get_contents($path.'/'.$file);
                        $pos = strpos($filesource, $find);
                        if ($pos === false) {
                            continue;
                        } else {
                            echo "The string '$find' was found in the file '$path/$file and exists at position $pos<br />";
                        }
                    }else{
                        continue;
                    }
                }
            }
        }
        closedir($handle);
    }
    $return = ob_get_contents();
    ob_end_clean();
    return $return;
}
exit();
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.deal",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/crm/company/edit/#company_id#/",
		"PATH_TO_INVOICE_SHOW" => "/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/crm/lead/convert/#lead_id#/",
		"PATH_TO_USER_PROFILE" => "/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $_REQUEST["deal_id"],
		"SEF_FOLDER" => "/crm/deal/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#deal_id#/",
			"show" => "show/#deal_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>