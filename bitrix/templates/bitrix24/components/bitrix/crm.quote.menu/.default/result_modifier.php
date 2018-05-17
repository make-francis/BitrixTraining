<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

// CrmQuoteDownloadPdf() points to:
// http://jygatech.inthemake.bz/crm/quote/payment/3/?pdf=1&DOWNLOAD=Y&ncc=1&PAY_SYSTEM_ID=3&BLANK=N

if (is_callable(array('CSalePdf', 'isPdfAvailable')) && CSalePdf::isPdfAvailable()) {

    // echo '<pre>';
    // // print_r($arResult['BUTTONS']);
    // print_r($arParams);
    // echo '</pre>';
    // echo '<pre>';
    // // print_r($arResult['BUTTONS']);
    // print_r($arResult);
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
        foreach ($languages as $lkey => $lang) {
            $lang = strtoupper($lang);
            
            $path = '/crm/quote/render.php?elementid='.intval($arParams['ELEMENT_ID']).'&request=render&pagemode=quote&tempid=1&fileformat=pdf&targetfolderid=42'; // folder 42 = company drive/templates
            $path .= '&lang='.$lkey;

            $links[] = array(
                    'DEFAULT' => true,
                    'TEXT' => "Download PDF ({$lang})",
                    'TITLE' => "Download quote PDF in {$lang} Translation",
                    // 'ONCLICK' => "BX.onCustomEvent(window, 'CrmQuoteDownloadPdf', [this, { blank: false }])"
                    'ONCLICK' => "jsUtils.Redirect(null, '".CHTTP::urlAddParams(
                            CComponentEngine::MakePathFromTemplate(
                                // $arParams['PATH_TO_INVOICE_PAYMENT'],
                                $path,
                                array('quote_id' => $arParams['ELEMENT_ID'])
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
