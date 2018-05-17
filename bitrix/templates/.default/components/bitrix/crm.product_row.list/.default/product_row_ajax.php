<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("catalog");
CModule::IncludeModule('crm');
global $DB;

if(isset($_POST) && !empty($_POST))
{
	$mode = $_POST['MODE'];
	$type = $_POST['OWNER_TYPE'];
	
	if($mode === 'SAVE_PRODUCTS')
	{
		$ID = $_POST['OWNER_ID'];
		$prodJson = isset($_POST['PRODUCT_ROW_DATA']) ? strval($_POST['PRODUCT_ROW_DATA']) : '';
		$arProducts = $arResult['PRODUCT_ROWS'] = strlen($prodJson) > 0 ? CUtil::JsObjectToPhp($prodJson) : array();
		
		switch($type){
			case "D": // Deal
				//CCrmDeal::SaveProductRows($ID, $arProducts, $checkPerms = true, $regEvent = true, $syncOwner = true);
				$result = CCrmDeal::SaveProductRows($ID, $arProducts, false);
				break;
			
			case "Q": //Quote
				$result = CCrmQuote::SaveProductRows($ID, $arProducts, false);
				break;
					
			default;
				break;
		}
		
		echo CUtil::PhpToJSObject($result);
	}
	elseif($mode == "GET_PART_NUM")
	{
		$props = CCatalogProduct::GetByIDEx($_POST["PRODUCT_ID"]);
		$prop_part_num = $props["PROPERTIES"]["PART"]["VALUE"];
	
		echo json_encode(array("PART_NUM"=>$prop_part_num));
	}
}
?>
