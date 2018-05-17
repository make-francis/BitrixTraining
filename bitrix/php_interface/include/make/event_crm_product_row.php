<?
AddEventHandler("crm", "OnAfterCrmDealProductRowsSave", "afterProductRowsSave");
AddEventHandler("crm", "OnAfterCrmQuoteProductRowsSave", "afterProductRowsSave");
AddEventHandler("crm", "onAfterCrmInvoiceSetStatus", "beforeCrmInvoiceEdit");
#AddEventHandler("crm", "OnAfterCrmProductUpdate", "afterCrmProductUpdate");

function afterProductRowsSave( $ELEMENT_ID, $arRows ) {
	global $DB;
	
	if(isset($_REQUEST["deal_id"]) && !empty($_REQUEST["deal_id"])){
		$type = "DEAL";
		$OWNER_TYPE_ID = "D";
	}
	elseif(isset($_REQUEST["quote_id"]) && !empty($_REQUEST["quote_id"])){
		$type = "QUOTE";
		$OWNER_TYPE_ID = "Q";
	}
	elseif(isset($_REQUEST["lead_id"]) && !empty($_REQUEST["lead_id"])){
		$type = "LEAD";
		$OWNER_TYPE_ID = "L";
	}elseif(isset($_POST["PERMISSION_ENTITY_TYPE"]) && !empty($_POST["PERMISSION_ENTITY_TYPE"])){
		$type = $_POST["PERMISSION_ENTITY_TYPE"];
		$OWNER_TYPE_ID = $_POST["OWNER_TYPE"];
	}
	
	deleteAll($ELEMENT_ID, $type);
	
	//get submitted product rows
	foreach($arRows as $key => $product):
		$product_row_id = getProductRowID($ELEMENT_ID, $OWNER_TYPE_ID, $product["PRODUCT_ID"], $product["PRODUCT_NAME"]);
		$dbres = $DB->Query("SELECT `ID` FROM `make_crm_product_row_part` WHERE `PRODUCT_ROW_ID`='".$product_row_id."' AND `TYPE`='".$type."'")->Fetch();
		
		if($dbres["ID"]>0)
		{
			$ID = $dbres["ID"];
		}
		
		$DB->PrepareFields("make_crm_product_row_part");
		$arFields = array(
			"PRODUCT_ROW_ID"          => "'".$product_row_id."'",
			"PART_NUM"                => "'".$product["PART_NUM"]."'",
			"PRODUCT_NAME"            => "'".$product["PRODUCT_NAME"]."'",
			"PRODUCT_ID"              => intval($product["PRODUCT_ID"]),
			"ELEMENT_ID"              => intval($ELEMENT_ID),
			"TYPE"										=> "'".$type."'"
		);
		
		$DB->StartTransaction();

		if($ID>0)
		{
			$DB->Update("make_crm_product_row_part", $arFields, "WHERE ID='".$ID."'", $err_mess.__LINE__);
			$DB->Commit();
		}
		else 
		{
			$DB->Insert("make_crm_product_row_part", $arFields, $err_mess.__LINE__);
			$DB->Commit();
		}
	endforeach;
	
	return $arRows;
}

function beforeCrmInvoiceEdit($arInvoice){
	global $DB;
	$product_rows = json_decode($_POST["INVOICE_PRODUCT_DATA"]);
	$ELEMENT_ID = $arInvoice["ID"];
	$type = "INVOICE";
	$OWNER_TYPE_ID = "I";
	
	deleteAll($ELEMENT_ID, $type);
	
	foreach($product_rows as $key => $product):
		//$product_row_id = getProductRowID($ELEMENT_ID, $OWNER_TYPE_ID, $product->PRODUCT_ID, $product->PRODUCT_NAME);
		
		$sql_string = "SELECT `ID` FROM `b_sale_basket` WHERE `ORDER_ID`='".$ELEMENT_ID."' AND `NAME`='".$product->PRODUCT_NAME."'";
		$productRow = $DB->Query($sql_string)->Fetch();
		$product_row_id = $productRow["ID"];
		
		$dbres = $DB->Query("SELECT `ID` FROM `make_crm_product_row_part` WHERE `PRODUCT_ROW_ID`='".$product_row_id."' AND `TYPE`='".$type."'")->Fetch();
		
		if($dbres["ID"]>0)
		{
			$ID = $dbres["ID"];
		}
		
		$DB->PrepareFields("make_crm_product_row_part");
		$arFields = array(
			"PRODUCT_ROW_ID"          => "'".$product_row_id."'",
			"PART_NUM"                => "'".$product->PART_NUM."'",
			"PRODUCT_NAME"            => "'".$product->PRODUCT_NAME."'",
			"PRODUCT_ID"              => intval($product->PRODUCT_ID),
			"ELEMENT_ID"              => intval($ELEMENT_ID),
			"TYPE"										=> "'".$type."'"
		);
		$DB->StartTransaction();

		if($ID>0)
		{
			$DB->Update("make_crm_product_row_part", $arFields, "WHERE ID='".$ID."'", $err_mess.__LINE__);
			$DB->Commit();
		}
		else 
		{
			$DB->Insert("make_crm_product_row_part", $arFields, $err_mess.__LINE__);
			$DB->Commit();
		}
	endforeach;
	
	return $arInvoice;
}

function getProductRowID($owner_id, $type, $product_id, $product_name){
	global $DB;
	
	if($product_id>0)
	{
		$sql_string = "SELECT `ID` FROM `b_crm_product_row` WHERE `OWNER_ID`='".$owner_id."' AND `OWNER_TYPE`='".$type."' AND `PRODUCT_ID`='".$product_id."'";
	}
	else
	{
		$sql_string = "SELECT `ID` FROM `b_crm_product_row` WHERE `OWNER_ID`='".$owner_id."' AND `OWNER_TYPE`='".$type."' AND `PRODUCT_NAME`='".$product_name."'";
	}
	
	$productRow = $DB->Query($sql_string)->Fetch();
	
	return $productRow["ID"];
}

function deleteByID($id){
	global $DB;
	
	$sql_command = "DELETE FROM `make_crm_product_row_part` WHERE `PRODUCT_ROW_ID`='".$id."'";
	$DB->Query($sql_command);
	
	return $id;
}

function deleteAll($element_id, $type){
	global $DB;
	
	$sql_command = "DELETE FROM `make_crm_product_row_part` WHERE `ELEMENT_ID`='".$element_id."' AND `TYPE`='".$type."'";
	$DB->Query($sql_command);
	
	return $element_id;
}

function afterCrmProductUpdate($ID, $arFields){
	echo "<pre>";
	print_r($arFields);
	echo "</pre>";
	die("xxx");
}
?>
