<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?>
<?
$iblock = $_GET['iblock'];
$element = $_GET['element'];



$getElement = CIBlockElement::GetList(Array("RAND" => "ASC"), Array("IBLOCK_ID"=>$iblock), false, Array("nTopCount" => 1), Array());
$elemento = $getElement->Fetch();

$prop = CIBlockElement::GetProperty($iblock, $element, Array());
$result = $prop->Fetch();
$by = $result['VALUE'];
$result2 = $prop->Fetch();
$quote = $result2['VALUE'];



$user = CUser::GetByID($by);
$userres = $user->Fetch();



echo "<br><br><br><br>";?>

		
<div class="user-profile-block-wrap">
   <div class="user-profile-block-wrap-l">
   	<?if($pic = CFile::GetPath($userres['PERSONAL_PHOTO'])){?>
			<span class="user-avatar" style="background: url(<?=$pic?>)"></span>
		<?}
		else{?>
			<span class="user-avatar user-default-avatar"></span>
		<?}
		?>
<table class="user-profile-block" cellspacing="0">
         <tbody>
            <tr>
               <td class="user-profile-block-title" colspan="2">General Information</td>
            </tr>
            <tr>
               <td><br></td>
            </tr>
            <tr>
               <td class="user-profile-nowrap">Name of Item:</td>
               <td><?=$elemento['NAME']?></td>
            </tr>
            <!--otp-->
            <!-- // otp -->
            <tr>
               <td class="user-profile-nowrap">Details:</td>
               <td><?=$elemento['DETAIL_TEXT']?></td>
            </tr>
            <tr>
               <td class="user-profile-nowrap">Date Created:</td>
               <td><?=$elemento['DATE_CREATE']?></td>
            </tr>
            <tr>
               <td class="user-profile-nowrap">Created By:</td>
               <td><?=$userres['NAME']." ".$userres['LAST_NAME']?></td>
            </tr>
            <tr>
               <td class="user-profile-nowrap">Quote:</td>
               <td><q><?=$quote?></q></td>
            </tr>
         </tbody>
      </table>

		</div>
</div>




<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>