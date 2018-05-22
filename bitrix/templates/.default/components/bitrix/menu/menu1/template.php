<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



<?
/*if(CModule::IncludeModule("iblock"))
{

   $items = GetIBlockSectionList('28', '', 
            Array("sort"=>"asc"), 10);

   while($arItem = $items->GetNext())
   {
      echo '<a href="catalog.php?bid='.urlencode($_get['bid']).
           '&ID='.$arItem['ID'].'">'.$arItem["NAME"].'</a><br>';
      echo $arItem["DESCRIPTION"]."<br>";
   }
}
else{
   ShowError("The module is not installed");
}*/


$blocktype = CIBlockType::GetByIDLang("webshop", LANGUAGE_ID);
if($blocktype!==false) {


	$resBlock = CIBlock::GetList(Array(), Array('TYPE'=>'webshop'),false);
	$ar_Block = $resBlock->Fetch();


    $sectionFilter = Array('IBLOCK_ID'=>$ar_Block['ID']);

    $resSection = CIBlockSection::GetList(Array($by=>$order), $sectionFilter, false);


    while($sectionResult = $resSection->Fetch())
    {
        echo ' '.$sectionResult['NAME'].'<br>';

        $elementSelect = Array("ID", "NAME");
        $elementFilter = Array("IBLOCK_ID"=>$ar_Block['ID'],"SECTION_ID"=>$sectionResult['ID']);

        $resElement = CIBlockElement::GetList(Array(), $elementFilter, false, false, $elementSelect);

        while ($arFields = $resElement->Fetch())
        {
        	echo "-------".$arFields['ID']." ".$arFields['NAME'];
        	echo "<br/>";
        }
        echo "<br/>";
    }

}
?>


<?if (!empty($arResult)):?>
<ul class="left-menu">



<?


foreach($arResult as $arItem):
	if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
		continue;
?>
	<?if($arItem["SELECTED"]):?>
		<li><a href="<?=$arItem["LINK"]?>" class="selected"><?=$arItem["TEXT"]?></a></li>
	<?else:?>
		<li><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
	<?endif?>
	
<?endforeach?>

</ul>
<?endif?>