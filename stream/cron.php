<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?
$getElement = CIBlockElement::GetList(Array("RAND" => "ASC"), Array("IBLOCK_ID"=>28), false, Array("nTopCount" => 1), Array());

$result = $getElement->Fetch();

$price = CPrice::GetBasePrice($result['ID']);
$stock = CCatalogProduct::GetByID($result['ID']);

$getSection = CIBlockSection::GetByID($result['IBLOCK_SECTION_ID']);
$category = $getSection->Fetch();
?>

<!DOCTYPE html>
<html>
<head>
  <style>
  #product {
    border-collapse: collapse;
    width: 100%;
  }

  #product td, #product th {
    font-size: 200%;
    padding: 8px;
  }

  #product tr:nth-child(even){background-color: #f2f2f2;}


  #product th {
    font-size: 400%;
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #f2f2f2;}
  }
</style>
</head>

<body style="padding: 50px 100px;">
<table id="product" cellspacing="0" align="center">
         <tbody>
          <tr>
            <th colspan="2">Product Information</th>
          </tr>
          <tr>
            <td colspan="2"><br></td>
          </tr>
            <tr>
               <td>Product Name:</td>
               <td><?=$result['NAME']?></td>
            </tr>
            <tr>
               <td>Product ID:</td>
               <td><?=$result['ID']?></td>
            </tr>
            <tr>
               <td>Product Price:</td>
               <td><?="$".$price['PRICE']?></td>
            </tr>
            <tr>
               <td>Product Stock:</td>
               <td><?=$stock['QUANTITY']?></td>
            </tr>
            <tr>
               <td>Date Created:</td>
               <td><?=$result['DATE_CREATE']?></td>
            </tr>
            <tr>
               <td>Product Category:</td>
               <td><?=$category['NAME']?></td>
            </tr>
            <tr>
               <td>Product Preview:</td>
               <td><img src="<?=CFile::GetPath($result['PREVIEW_PICTURE'])?>"/></td>
            </tr>
         </tbody>
      </table>
</body>
</html>