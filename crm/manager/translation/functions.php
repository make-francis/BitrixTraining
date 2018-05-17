<?php

function getProductCustomFields()
{
    define('IBLOCK_ID', 27); // CRM_PRODUCT_CATALOG
    CModule::IncludeModule('crm');
    $dbRes = CIBlockProperty::GetList(array(),array('IBLOCK_ID' => IBLOCK_ID));
    $props = array();
    while ($row = $dbRes->Fetch()) {
        $props[] = $row;
    }

    return $props;
}

function getTranslations()
{
    global $DB;
    $query = 'SELECT * FROM m_report_translations';
    $result = $DB->Query($query);

    $data = array();
    while ($row = $result->Fetch()) {
        extract($row);

        $data[ $type ] [ $language ][ $code ] = $row;
    }
    
    return $data;
}

function setTranslations()
{
    global $DB;

    if (!empty($_POST['translate'])) {

        foreach ($_POST['translate'] as $key => $langText) {
            $type = 'quote';
            if (strpos($key, 'LI_')===0) {
                $type = 'invoice';
            }

            foreach ($langText as $lang => $text) {
                // clear old lang assignments first
                $truncate = 'DELETE FROM m_report_translations WHERE code="'.$key.'" AND language="'.strtolower($lang).'"';
                $DB->Query($truncate);

                $text = trim($text);
                if (empty($text)) {
                    continue;
                }

                // insert new translation values
                $query = 'INSERT INTO m_report_translations (`code`, `text`, `language`, `type`) VALUES ("'.$key.'", "'.$text.'", "'.strtolower($lang).'", "'.strtolower($type).'")';
                $DB->Query($query);
            }
        }
    }

    return true;
}


function getTranslationDefaults()
{
    $defaults = array(
        'QUOTE' => array(
            // Quote Labels
            'LQ_H_TYPE' => 'Quotation / Contract',
            'LQ_H_NUM' => 'Quotation number',
            'LQ_H_DATE' => 'Date',
            'LQ_H_EXPIRES' => 'Expiration Date',
            'LQ_H_SALEMAN' => 'Salesman',
            'LQ_H_GST' => 'G.S.T',
            'LQ_H_TVQ' => 'T.V.Q',
            'LQ_B_HEAD' => 'PURCHASER',
            'LQ_B_TITLE' => '',
            'LQ_B_ADDR' => 'Address',
            'LQ_B_CITY' => 'City',
            'LQ_B_ZIP' => 'ZIP Code',
            'LQ_B_TEL' => 'Tel',
            'LQ_B_FAX' => 'Fax',
            'LQ_B_CONTACT' => 'Contact',
            'LQ_B_CELL' => 'Cell',
            'LQ_B_EMAIL' => 'E-mail',
            'LQ_D_HEAD' => 'DELIVERED TO',
            'LQ_D_TITLE' => 'Farm',
            'LQ_D_ADDR' => 'Address',
            'LQ_D_CITY' => 'City',
            'LQ_D_ZIP' => 'ZIP Code',
            'LQ_D_TEL' => 'Tel',
            'LQ_D_FAX' => 'Fax',
            'LQ_D_CONTACT' => 'Contact',
            'LQ_D_CELL' => 'Cell',
            'LQ_D_EMAIL' => 'E-mail',
            'LQ_PURCHASE_TXT' => 'Purchaser agrees to purchase from the vendor the following property, according to the provisions set out below:',
            'LQ_TH_PARTNO' => 'PART NUMBER',
            'LQ_TH_DESC' => 'DESCRIPTION',
            'LQ_TH_NOTE' => 'NOTE',
            'LQ_TH_QTY' => 'QTY',
            'LQ_TH_UNITPRICE' => 'UNIT PRICE',
            'LQ_TH_DISCOUNT' => 'DISCOUNT',
            'LQ_TH_NETPRICE' => 'NET PRICE',
            'LQ_TH_TOTAL' => 'TOTAL',
            'LQ_TH_SUBTOTAL' => 'Subtotal',
            'LQ_TF_CURRENCY' => 'Currency: USD',
            'LQ_TF_FINALTOTAL' => '',
            'LQ_TF_DISCOUNT_RATE' => '',
            'LQ_TF_COMMENTS' => 'COMMENTS',
            'LQ_TF_DISCOUNT' => 'EARLY PAYMENT DISCOUNT',
            'LQ_TF_NET_PAY' => 'Net if paid before 10 days',
            'LQ_TF_NOTE_1_TXT' => 'P.S. Gestal unit needs a conventional electrical outlet for its functioning. This one is not included in the price and shall be provided by the purchaser.',
            'LQ_TF_NOTE_2_TXT' => 'Warranty: 1 year parts and labor at the plant according to Jyga Technologies’ standard limited warranty',
            'LQ_TF_NOTE_3_TXT' => 'INSTALLATION NOT INCLUDED UNLESS OTHERWISE INDICATED',
            'LQ_TF_NOTE_4_TXT' => 'NOTE: Applicable taxes are the responsibility of the purchaser',
            'LQ_TF_ESTIMATE_SHIP' => 'Estimated delivery date:',
            'LQ_TF_INITIALS' => 'Initials:',
            'LQ_TF_DISCLAIMER_TXT' => 'This contract shall be formed only when and where it shall receive the acceptance of a JYGA Technologies Inc. duly authorized officer.',
            'LQ_TERMS_CONDITIONS' => '',
            'LQ_TERMS_SHIP_HEAD' => 'DELIVERY:',
            'LQ_TERMS_SHIP_TXT' => "Once the delivery has been the property shall be at the purchaser’s own risk. Nevertheless, the vendor shall not be liable for any delay in the delivery arising from any transportation problem, fire, labor dispute, superior force or any other circumstance out of vendor’s control.",
            'LQ_TERMS_SHIP_ADDTL_TXT' => "Purchaser agrees to take delivery of property within forty-eight (48) hours following the vendor’s notice that the property is ready for delivery. If the purchaser refuses or neglects to take delivery within that delay, he will suffer a penalty, which is stipulated for mere delay in performance of the obligation, representing the liquidated anticipated damages owned to vendor and assessed by the parties to 20% of the total sale price.",
            'LQ_TERMS_WARRANTY_HEAD' => "WARRANTY:",
            'LQ_TERMS_WARRANTY_TXT' => "The vendor warrants the purchaser that the property if free of all rights except those he has declared at the time of the sale. The property is subject to no other warranty whatsoever, except the one given by the manufacturer the content of which is well known by the purchaser.",
            'LQ_TERMS_WARRANTY_ADDTL_TXT' => "",
            'LQ_TERMS_CONVEY_HEAD' => "CONVEYANCE OF OWNERSHIP:",
            'LQ_TERMS_CONVEY_TXT' => "According to this contract, the transfer of ownership of the property sold does not arise at the time of the formation of the contract, but only at the time of the full payment of the sale price: the title to the property sold remaining with the vendor until the entire price is paid in full.",
            'LQ_TERMS_RESOLVE_HEAD' => "RESOLUTION OF THE SALE:",
            'LQ_TERMS_RESOLVE_TXT' => "If, for any reason, the manufacturer is unable to deliver the material ordered, the sale shall be forthwith resolved and the vendor shall reimburse the deposit put down by the purchaser. However, if used property has been given by purchaser as down payment and vendor has already sold the property, the vendor shall reimburse the sale price received, less 15% for his costs.

The reimbursement made by the vendor shall constitute and absolute discharge for any claim that the purchaser might be entitled under this contract.",
            'LQ_TERMS_DEFS_HEAD' => "DEFAULTS:",
            'LQ_TERMS_DEFS_TXT' => "The purchaser shall be in default, according to this contract, by the mere lapse of time, if he fails to perform any one of his obligations at maturity.",
            'LQ_SIGN_DATE' => 'Date',
            'LQ_SIGN_PLACE' => 'Place of Signature',
            'LQ_SIGN_BUYER' => "Purchaser’s signature",
            'LQ_SIGN_ACCEPT_ON' => "Accepted this on",
            'LQ_SIGN_ACCEPT_AT' => "at",
            'LQ_SIGN_AUTHED_TXT' => "Duly authorized officier of Jyga Technologies",
            ),
        'INVOICE' => array(

            // Invoice Labels
            'LI_H_TYPE' => 'INVOICE',
            'LI_H_NUM' => 'Quotation number',
            'LI_H_DATE' => 'Date',
            'LI_H_EXPIRES' => 'Expiration Date',
            'LI_H_SALEMAN' => 'Salesman',
            'LI_H_GST' => 'G.S.T',
            'LI_H_TVQ' => 'T.V.Q',
            'LI_SL_TITLE' => 'SELLER:',
            'LI_SL_TEL' => 'Tel:',
            'LI_SL_FAX' => 'Fax:',
            'LI_B_TITLE' => 'PURCHASER:',
            'LI_B_NAME' => 'Name:',
            'LI_B_ADDR' => 'Address:',
            'LI_B_CITY' => 'City:',
            'LI_B_ZIP' => 'ZIP Code:',
            'LI_B_TEL' => 'Tel:',
            'LI_B_FAX' => 'Fax:',
            'LI_B_CONTACT' => 'Contact:',
            'LI_B_CELL' => 'Cell:',
            'LI_B_EMAIL' => 'E-mail:',
            'LI_S_TITLE' => 'DELIVERED TO:',
            'LI_S_ADDR' => 'Address:',
            'LI_S_CITY' => 'City:',
            'LI_S_ZIP' => 'ZIP Code:',
            'LI_S_TEL' => 'Tel:',
            'LI_S_FAX' => 'Fax:',
            'LI_S_CONTACT' => 'Contact:',
            'LI_S_EMAIL' => 'E-mail:',
            'LI_S_TITLE' => 'E-mail:',
            'LI_S_TITLE' => 'E-mail:',
            'LI_PURCHASE_AGREE_TXT' => 'Purchaser agrees to purchase from the vendor the following property, according to the provisions set out below:',
            'LI_TH_DESC' => 'DESCRIPTION',
            'LI_TH_NOTE' => 'NOTE',
            'LI_TH_QTY' => 'QTY',
            'LI_TH_UNITPRICE' => 'UNIT PRICE',
            'LI_TH_DISC' => 'DISCOUNT',
            'LI_TH_DISC_AMT' => 'DISCOUNT AMOUNT',
            'LI_TH_TOTAL' => 'TOTAL',
            'LI_FINAL_SUBTOTAL' => 'Subtotal',
            'LI_FINAL_DISCOUNT' => 'Total Discount',
            'LI_FINAL_TAX' => 'Taxes',
            'LI_FINAL_TOTAL' => 'Total',
            ),
        );

    return $defaults;
}
