<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalCss(SITE_TEMPLATE_PATH."/css/sidebar.css");

$this->setFrameMode(true);


$this->SetViewTarget("sidebar", 1);
$getElement = CIBlockElement::GetList(Array("RAND" => "ASC"), Array("IBLOCK_ID"=>29), false, Array("nTopCount" => 1), Array("ID" , "IBLOCK_ID" , "NAME"));
$elemento = $getElement->Fetch();

$prop = CIBlockElement::GetProperty($elemento['IBLOCK_ID'], $elemento['ID'], Array());
$result = $prop->Fetch();
$by = $result['VALUE'];
$result2 = $prop->Fetch();
$qoute = $result2['VALUE'];



$user = CUser::GetByID($by);
$userres = $user->Fetch();

$name = $userres['NAME']." ".$userres['LAST_NAME'];

$loc = "details.php?iblock=".$elemento['IBLOCK_ID']."&&element=".$elemento['ID'];

?>

<div class="sidebar-widget sidebar-widget-qoute" style="margin-top: 5px; min-height: 130px;">
	<div class="sidebar-widget-top">
		<div class="sidebar-widget-top-title">QUOTE OF THE DAY</div>
	</div>

	<a href="<?=$loc?>" class="sidebar-widget-item widget-last-item">
		<?
		if($pic = CFile::GetPath($userres['PERSONAL_PHOTO'])){?>
		<span class="user-avatar" style="background: url(<?=$pic?>) no-repeat center;background-size: 100%;"></span>
		<?}
		else{?>
			<span class="user-avatar user-default-avatar"></span>
		<?}
		?>
		
		<span class="sidebar-user-info">
			<span class="user-post-name"><?=$name?></span>
		</span>
		<span class="sidebar-user-info">
			<span><q><?=$qoute?></q></span>
		</span>
	</a>

</div>



