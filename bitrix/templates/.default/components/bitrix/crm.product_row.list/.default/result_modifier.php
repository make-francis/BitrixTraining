<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

foreach($arResult["PRODUCT_ROWS"] as $key => $row):
	$props = CCatalogProduct::GetByIDEx($row["PRODUCT_ID"]);
	$prop_part_num = $props["PROPERTIES"]["PART"]["VALUE"];
	
	
	//check value from make_crm_product_row_part
	$element_id = $arResult["OWNER_ID"];
	$dbres = $DB->Query("SELECT `PART_NUM` FROM `make_crm_product_row_part` WHERE `ELEMENT_ID`='".$element_id."' AND `PRODUCT_NAME`='".$row["PRODUCT_NAME"]."' AND `PRODUCT_ID`='".$row["PRODUCT_ID"]."'")->Fetch();
	if(!empty($dbres["PART_NUM"]) || !is_null($dbres["PART_NUM"]))
	{
		$prod_part_num = $dbres["PART_NUM"];
	}
	
	$arResult["PRODUCT_ROWS"][$key]["PART_NUM"] = (!empty($prod_part_num) && $prod_part_num != $prop_part_num) ? $prod_part_num : $prop_part_num;
endforeach;
?>
