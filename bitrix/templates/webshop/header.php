<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/template_styles.css")?>" type="text/css" rel="stylesheet" />
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/css/bootstrap.css")?>" type="text/css" rel="stylesheet" />
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/css/font-awesome.min.css")?>" type="text/css" rel="stylesheet" />
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/ItemSlider/css/main-style.css")?>" type="text/css" rel="stylesheet" />

<?
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery-1.10.2.js", true);
$APPLICATION->showHead();

?>
<title><? $APPLICATION->showTitle(); ?></title>
</head>

<body class="<?$APPLICATION->showProperty("BodyClass")?>">
    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><strong>DIGI</strong> Shop</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">


                <ul class="nav navbar-nav navbar-right">
                    <li><a href="index.php">Homepage</a></li>
                    <li><a href="#" data-toggle="modal" data-target="#myModal">Check Order</a></li>
                    <li><a href="#">Signup</a></li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">24x7 Support <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><strong>Call: </strong>+09-456-567-890</a></li>
                            <li><a href="#"><strong>Mail: </strong>info@yourdomain.com</a></li>
                            <li class="divider"></li>
                            <li><a href="#"><strong>Address: </strong>
                                <div>
                                    234, New york Street,<br />
                                    Just Location, USA
                                </div>
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" role="search">
                    <div class="form-group">
                        <input type="text" placeholder="Enter Keyword Here ..." class="form-control">
                    </div>
                    &nbsp; 
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>



    <!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Shopping Cart</h4>
      </div>
      <div class="modal-body" style="max-height:600px;overflow:auto;">

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

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>







    <div class="container" style="min-height:752px;">
        <div class="row">
            <?
            if($_SERVER['REQUEST_URI'] == "/site_webshop/basket.php"){

            }
            else{

            $APPLICATION->IncludeComponent("bitrix:menu", "menu1", 
                Array(
                "ROOT_MENU_TYPE" => "top",
                "MAX_LEVEL" => "1",
                "CHILD_MENU_TYPE" => "top",
                "USE_EXT" => "Y",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N",
                "MENU_CACHE_TYPE",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "MENU_CACHE_GET_VARS" => "")
            );
        }
    ?>





        