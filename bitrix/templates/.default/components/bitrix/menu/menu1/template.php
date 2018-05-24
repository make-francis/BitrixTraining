<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="col-md-3">
  <div>
    <a href="#" class="list-group-item active">Electronics</a>
    <ul class="list-group">

<?
$blocktype = CIBlockType::GetByIDLang("webshop", LANGUAGE_ID);
if($blocktype!==false) {


  $resBlock = CIBlock::GetList(Array(), Array('TYPE'=>'webshop'),false);
	$ar_Block = $resBlock->Fetch();


    $sectionFilter = Array('IBLOCK_ID'=>$ar_Block['ID']);

    $resSection = CIBlockSection::GetList(Array('IBLOCK_ID'=>'ASC'), $sectionFilter, false);

while($sectionResult = $resSection->Fetch())
    {
    
?>
        <li class="list-group-item"><a href="?BlockID=<?=$ar_Block['ID']?>&&SecID=<?=$sectionResult['ID']?>"><?=$sectionResult['NAME']?></a>
          <span class="label label-success pull-right"></span> 
        </li>

<?
    }

}
?>

</ul>
</div>
</div>