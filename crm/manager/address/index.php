<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle('Address Manager');

include 'functions.php';

if (isset($_POST['form_address'])) {
    assignAddress();
}
?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
<div class="container col-lg-12">
    <div class="row">
        <div class="col-lg-12">
          <h2>Addresses</h2>
          <p>Select the address fields to be used when generating Invoice/Quote files:</p>
          <div class="panel-group">
            <form action="" method="post">
                <input type="hidden" name="form_address" value="1">
                <div class="panel panel-default">
                  <div class="panel-heading"></div>
                  <div class="panel-body">
                    <?php
                    $cid = 7; // Test II Co.
                    $company = getCompanyAddress($cid);
                    ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>
                                    <label>Sample Company:</label>
                                </th>
                                <th colspan="2">
                                    <label><?php echo $company['company']['TITLE']; ?></label>
                                </th>
                            </tr>
                            <tr>
                                <th>&nbsp;</th>
                                <th>
                                    <label>Select Address</label>
                                </th>
                                <th>
                                    <label>Type</label>
                                </th>
                                <th>
                                    <label>Preview</label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $saved = $translations['en']['address'];
                            $reportAddresses = getReportAddresses();
                            $addressTypes = getAddressTypes();
                            ?>
                            <?php foreach ($reportAddresses as $key => $address): ?>
                            <tr>
                                <td scope="row" width="50px">
                                    <input type="hidden" class="hidden" name="address[<?=$key?>]" value="<?=$key?>">
                                </td>
                                <td width="250px"><?=$address?></td>
                                <td width="250px">
                                    <select id="<?=$key?>" class="address_type" name="address_type[<?=$key?>]">
                                        <?php foreach ($addressTypes as $pkey => $pval): ?>
                                            <?php
                                            $selected = '';
                                            if ($saved[$key]['prop_id']==$pkey) {
                                                $selected = 'selected';
                                            }
                                            ?>
                                            <option value="<?=$pkey?>" <?=$selected?>><?=$pval?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <?php foreach ($company['address'] as $akey => $row) : $hidden=($akey!=1)?'hidden':''; ?>
                                        <p class="address_type_<?=$key?> <?=$key?>_<?=$akey?> <?=$hidden?>">
                                            <?=$row['ADDRESS_1']?> <?=$row['ADDRESS_2']?>
                                            <br>
                                            <?=$row['CITY']?> <?=$row['POSTAL_CODE']?>
                                            <br>
                                            <?=$row['REGION']?> <?=$row['PROVINCE}']?>
                                            <br>
                                            <?=$row['COUNTRY']?>
                                        </p>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                  </div>
                  <div class="panel-footer">
                        <input type="submit" class="btn btn-secondary" value="Save">
                    </div>
                </div>
            </form>
            
          </div>
        </div>
    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" 
    integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" 
    crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script>
$(function() {
    $('.address_type').on('change', function() {
    var type = $(this).attr('id');
    var akey = $(this).find('option:selected').val();
    $('.address_type_'+type).addClass('hidden');
    $('.'+type+'_'+akey).removeClass('hidden');
    });
});
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
