<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle('Address and Translation Manager');

include 'functions.php';

if (isset($_POST['post_translations'])) {
    setTranslations();
}

$translated = getTranslations();
$languages = getCurrentLanguages();
$defaults = getTranslationDefaults();
?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
<style>
.table>tbody>tr>td {
    padding: 15px 0 15px 10px;
    vertical-align: middle;
}
</style>
<div class="container col-lg-12">
    <div class="row">
        <div class="">
          <h2>Translations</h2>
          <p>Set the custom field translations used in Invoice/Quote reports:</p>
          <div class="panel-group">
            <form action="" method="post">
                <input type="hidden" class="hidden" name="post_translations">
                <input type="hidden" name="form_quote" value="1">
                <div class="panel panel-default">
                  <div class="panel-heading">QUOTE</div>
                  <div class="panel-body">
                    <table class="table table-striped" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>CODE</th>
                                <th>
                                    <label for="language">Default/English</label>
                                </th>
                                <?php
                                foreach ($languages as $lkey => $lang): if (strtolower($lkey)=='en') continue; ?>
                                    <th><?=$lang?></th>
                                <?php endforeach; reset($languages); ?>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php $type = 'quote'; foreach ($defaults['QUOTE'] as $key => $defaultText): ?>
                            <tr>
                                <td>
                                    <?=$key?>
                                </td>
                                <?php foreach ($languages as $lkey => $lang): ?>
                                    <?php
                                    $lkey = strtolower($lkey);
                                    $langText = $translated[$type][$lkey][$key]['text'];
                                    ?>
                                    <td>
                                    <?php if (stripos($key, '_TXT')!==false): ?>
                                        <textarea name="translate[<?=$key?>][<?=$lkey?>]" cols="30" rows="10"><?=$langText?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="field_val" name="translate[<?=$key?>][<?=$lkey?>]" value="<?=$langText?>">
                                    <?php endif; ?>
                                    
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                  </div>
                  <div class="panel-heading">INVOICE</div>
                  <div class="panel-body">
                    <table class="table table-striped" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>CODE</th>
                                <th>
                                    <label for="language">Default/English</label>
                                </th>
                                <?php
                                foreach ($languages as $lkey => $lang): if (strtolower($lkey)=='en') continue; ?>
                                    <th><?=$lang?></th>
                                <?php endforeach; reset($languages); ?>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php $type = 'invoice'; foreach ($defaults['INVOICE'] as $key => $defaultText): ?>
                            <tr>
                                <td>
                                    <?=$key?>
                                </td>
                                <?php foreach ($languages as $lkey => $lang): ?>
                                    <?php
                                    $lkey = strtolower($lkey);
                                    $langText = $translated[$type][$lkey][$key]['text'];
                                    ?>
                                    <td>
                                    <?php if (stripos($key, '_TXT')!==false): ?>
                                        <textarea name="translate[<?=$key?>][<?=$lkey?>]" cols="30" rows="10"><?=$langText?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="field_val" name="translate[<?=$key?>][<?=$lkey?>]" value="<?=$langText?>">
                                    <?php endif; ?>
                                    
                                    </td>
                                <?php endforeach; ?>
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
            <br>
            <br>
          </div>
        </div>
    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" 
    integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" 
    crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
