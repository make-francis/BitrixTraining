<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (is_callable(array('CSalePdf', 'isPdfAvailable')) && CSalePdf::isPdfAvailable()) {

    // echo '<pre>';
    // print_r($arResult['BUTTONS']);
    // echo '</pre>';
    // exit();
    
    $replaceKey = null;
    foreach ($arResult['BUTTONS'] as $key => $buttonArray) {
        if ('btn-download'  == strtolower($buttonArray['ICON'])) {
            if (isset($buttonArray['LINKS']) && !empty($buttonArray['LINKS'])) {
                $linksArray = current($buttonArray['LINKS']);
                if ('download pdf' == strtolower($linksArray['TEXT'])) {
                    $replaceKey = intval($key);
                    break;
                }
            }
        }
    }
    

    if (!is_null($replaceKey) && isset($arResult['BUTTONS'][ $replaceKey ])) {
        unset($arResult['BUTTONS'][ $replaceKey ]['LINKS']);

        // @todo: get languages used
        $languages = getCurrentLanguages(); // INTERFACE/include/make/helpers.php

        $links = array();
        foreach ($languages as $lang) {
            $lang = strtoupper($lang);
            $links[] = array(
                    'DEFAULT' => true,
                    'TEXT' => "Download PDF ({$lang})",
                    'TITLE' => "Download invoice PDF in {$lang} Translation",
                    'ONCLICK' => "jsUtils.Redirect(null, '".CHTTP::urlAddParams(
                            CComponentEngine::MakePathFromTemplate(
                                $arParams['PATH_TO_INVOICE_PAYMENT'],
                                array('invoice_id' => $arParams['ELEMENT_ID'])
                            ),
                            array('pdf' => 1, 'DOWNLOAD' => 'Y', 'ncc' => '1'))."')"
                );
        }
        $arResult['BUTTONS'][ $replaceKey ]['LINKS'] = $links;

        /*$arResult['BUTTONS'][] = array(
                'LINKS' => array(
                    array(
                        'DEFAULT' => true,
                        'TEXT' => GetMessage('INVOICE_PAYMENT_PDF'),
                        'TITLE' => GetMessage('INVOICE_PAYMENT_PDF_TITLE'),
                        'ONCLICK' => "jsUtils.Redirect(null, '".CHTTP::urlAddParams(
                                CComponentEngine::MakePathFromTemplate(
                                    $arParams['PATH_TO_INVOICE_PAYMENT'],
                                    array('invoice_id' => $arParams['ELEMENT_ID'])
                                ),
                                array('pdf' => 1, 'DOWNLOAD' => 'Y', 'ncc' => '1'))."')"
                    ),
                    array(
                        'TEXT' => GetMessage('INVOICE_PAYMENT_PDF_BLANK'),
                        'TITLE' => GetMessage('INVOICE_PAYMENT_PDF_BLANK_TITLE'),
                        'ONCLICK' => "jsUtils.Redirect(null, '".CHTTP::urlAddParams(
                                CComponentEngine::MakePathFromTemplate(
                                    $arParams['PATH_TO_INVOICE_PAYMENT'],
                                    array('invoice_id' => $arParams['ELEMENT_ID'])
                                ),
                                array('pdf' => 1, 'DOWNLOAD' => 'Y', 'BLANK' => 'Y', 'ncc' => '1'))."')"
                    )
                ),
                'TYPE' => 'toolbar-split-left',
                'ICON' => 'btn-download'
            );*/
    }
    
}
