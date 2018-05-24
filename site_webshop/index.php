<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>

            <div class="col-md-9" style="min-height:742px;">
                <div>
                    <ol class="breadcrumb">
                        <li><a href="#">Home</a></li>
                        <li class="active">Electronics</li>
                    </ol>
                </div>

                <div class="row">

                    <?
                    $elementSelect = Array("ID", "NAME","PREVIEW_PICTURE");
                    if(isset($_GET['BlockID'])){
                        $elementFilter = Array("IBLOCK_ID"=>$_GET['BlockID'],"SECTION_ID"=>$_GET['SecID']);
                        $resElement = CIBlockElement::GetList(Array(), $elementFilter, false, Array(), $elementSelect);
                    }
                    else{
                        $elementFilter = Array("IBLOCK_ID"=>28);
                        $resElement = CIBlockElement::GetList(Array("RAND" => "ASC"), $elementFilter, false, Array(), $elementSelect);
                    }

                    while ($arFields = $resElement->Fetch()){
                        $prodID = $arFields['ID'];
                    ?>

                    <div class="col-md-4 text-center col-sm-6 col-xs-6">
                        <div class="thumbnail product-box">
                            <img src="<?=CFile::GetPath($arFields['PREVIEW_PICTURE'])?>" alt="" />
                            <div class="caption">
                                <h3>
                                <?if (strlen($arFields['NAME']) > 15){
                                    $str = substr($arFields['NAME'], 0, 12) . '...';
                                    echo $str;
                                }
                                else{
                                    echo $arFields['NAME'];
                                }?>    
                                </h3>

                                <? $price = CPrice::GetBasePrice($arFields['ID'])?>
                                <p>Price : <strong>$<?=$price['PRICE']?></strong> </p>

                                <? $stock = CCatalogProduct::GetByID($arFields['ID'])?>
                                <p>Stock : <strong><?=$stock['QUANTITY']?></strong> </p>

                                <p><a href="add.php?ID=<?=$prodID?>" class="btn btn-success" role="button">Add To Cart</a>
                                <a href="#" class="btn btn-primary" role="button">See Details</a></p>
                            </div>
                        </div>
                    </div>
        <?}
?>
                </div>
            </div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>