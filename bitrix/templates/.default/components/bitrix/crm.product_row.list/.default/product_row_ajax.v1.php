<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("catalog");
global $DB;

if(isset($_POST) && !empty($_POST))
{
	/*
	$product_rows = json_decode($_POST["PRODUCT_ROWS"]);
	foreach($product_rows as $product):
		$dbres = $DB->Query("SELECT `ID` FROM `make_crm_product_row_part` WHERE `PRODUCT_ROW_ID`='".$product->ID."' AND `PRODUCT_NAME`='".$product->PRODUCT_NAME."' AND `PRODUCT_ID`='".$product->PRODUCT_ID."'")->Fetch();
		if($dbres["ID"]>0)
		{
			$ID = $dbres["ID"];
		}

		$DB->PrepareFields("make_crm_product_row_part");
		$arFields = array(
			"PRODUCT_ROW_ID"          => intval($product->ID),
			"PART_NUM"                => intval($product->PART_NUM),
			"PRODUCT_NAME"            => "'".$product->PRODUCT_NAME."'",
			"PRODUCT_ID"              => intval($product->PRODUCT_ID)
		);
		$DB->StartTransaction();

		if($ID>0)
		{
			$DB->Update("make_crm_product_row_part", $arFields, "WHERE ID='".$ID."'", $err_mess.__LINE__);
		}
		else 
		{
			$ID = $DB->Insert("make_crm_product_row_part", $arFields, $err_mess.__LINE__);
		}

		$DB->Commit();
	endforeach;

	echo json_encode(array("success"=>true));
	*/
	
	$props = CCatalogProduct::GetByIDEx($_POST["PRODUCT_ID"]);
	$prop_part_num = $props["PROPERTIES"]["PART"]["VALUE"];
	
	echo json_encode(array("PART_NUM"=>$prop_part_num));
}
?>
