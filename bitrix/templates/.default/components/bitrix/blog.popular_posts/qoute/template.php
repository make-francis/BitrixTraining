<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalCss(SITE_TEMPLATE_PATH."/css/sidebar.css");

$this->setFrameMode(true);


$this->SetViewTarget("sidebar", 250);
$getElement = CIBlockElement::GetList(Array("RAND" => "ASC"), Array("IBLOCK_ID"=>29), false, Array("nTopCount" => 1), Array());
$elemento = $getElement->Fetch();

?>

<div class="sidebar-widget sidebar-widget-qoute" style="margin-top: 5px;">
	<div class="sidebar-widget-top">
		<div class="sidebar-widget-top-title">Title</div>
	</div>

	<a href="" class="sidebar-widget-item widget-last-item">
		<span class="user-avatar user-default-avatar">
		</span>
		<span class="sidebar-user-info">
			<span class="user-post-name">a</span>
		</span>
	</a>

</div>



