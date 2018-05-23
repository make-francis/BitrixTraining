<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("BASKET"));
?>


<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", ".default",
    Array (
        "ACTION_VARIABLE" => "action" ,
        "AUTO_CALCULATION" => "Y" ,
        "TEMPLATE_THEME" => "blue" ,
        "COLUMNS_LIST" => array ( " NAME " , " DISCOUNT " , " WEIGHT " , "DELETE" , " DELAY " ), 
        "COMPONENT_TEMPLATE" => ".default" ,
        "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N" ,
        "GIFTS_BLOCK_TITLE" => "Choose one of the gifts" ,
        "GIFTS_CONVERT_CURRENCY" => "Y" ,
        "GIFTS_HIDE_BLOCK_TITLE" => "N" ,
        "GIFTS_HIDE_NOT_AVAILABLE " => "N" ,
        "GIFTS_MESS_BTN_BUY " => "Select" ,
        "GIFTS_MESS_BTN_DETAIL "=> "Learn more" ,
        "GIFTS_PAGE_ELEMENT_COUNT" => "4",
        "GIFTS_PRODUCT_PROPS_VARIABLE" => "prop" ,
        "GIFTS_PRODUCT_QUANTITY_VARIABLE" => "" ,
        "GIFTS_SHOW_DISCOUNT_PERCENT" => "Y" ,
        "GIFTS_SHOW_IMAGE" => "Y" ,
        "GIFTS_SHOW_NAME" => "Y" ,
        "GIFTS_SHOW_OLD_PRICE" => "Y" ,
        "GIFTS_TEXT_LABEL_GIFT" => "Gift" ,
        "GIFTS_PLACE" =>"BOTTOM" ,
        "HIDE_COUPON" => "N" ,
        "OFFERS_PROPS" => array ( "SIZES_SHOES" , "SIZES_CLOTHES" ),
        "PATH_TO_ORDER" => "/personal/order.php" ,
        "PRICE_VAT_SHOW_VALUE" => "N" ,
        "QUANTITY_FLOAT" => "N" ,
        "SET_TITLE" => "Y" ,
        "TEMPLATE_THEME" => "blue" ,
        "USE_GIFTS" => "Y" ,
        "USE_PREPAYMENT"=> "N"
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>