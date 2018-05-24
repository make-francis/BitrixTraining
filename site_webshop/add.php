<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_GET['ID'])){
    $id = $_GET['ID'];

    if (CModule::IncludeModule("catalog")){
        Add2BasketByProductID ( $id, 1, array());
        }
    }

?>
<script>
	alert('Successfully added to the cart.');
	window.location.href="<?=$_SERVER['HTTP_REFERER']?>";
</script>
