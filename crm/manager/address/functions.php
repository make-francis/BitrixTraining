<?php

function getCompany($cid)
{
    $company = intval($cid);
    global $DB;

    $query = 'SELECT * FROM b_crm_company WHERE ID="'.$company.'" LIMIT 1';
    $result = $DB->Query($query, false, $err_mess.__LINE__);
    if ($row = $result->Fetch()) {
        return $row;
    }

    return array();
}
function getCompanyAddress($cid)
{
    $company = intval($cid);
    global $DB;

    $getAddressTypes = getAddressTypes();

    $anchorType = 4; // 3 = contact, 4 = company
    $entityType = 8; // 8 = addresss
    
    $address = array();
    $address['company'] = getCompany($company);

    $query = "SELECT * FROM b_crm_addr WHERE ANCHOR_TYPE_ID = '".$anchorType."' AND ANCHOR_ID = '".$company."' AND ENTITY_TYPE_ID = '".$entityType."' ORDER BY TYPE_ID ASC";
    $result = $DB->Query($query, false, $err_mess.__LINE__);
    while ($row = $result->fetch()) {
        $address['address'][ $row['TYPE_ID'] ] = $row;
    }

    return $address;
}
function getReportAddresses()
{
    $types = array(
            'billing_address' => 'Billing Address',
            'delivery_address' => 'Delivery Address',
        );

    return $types;
}

function getAddressTypes()
{
    $types = array(
            1 => 'Street Address',            
            4 => 'Registered Address',            
            6 => 'Legal Address',            
            9 => 'Beneficiary Address',            
        );

    return $types;
}

function assignAddress()
{
    global $DB;
    $type = 'address';
    $lang = 'en';

    if (!empty($_POST['address_type'])) {
        // clear old lang assignments first
        $truncate = 'DELETE FROM m_field_translations WHERE language="'.$lang.'" AND type="'.$type.'"';
        $DB->Query($truncate);

        foreach ($_POST['address_type'] as $key => $value) {
            $name = strtolower($key);
            $query = "INSERT INTO m_field_translations (name, field_code, prop_id, type, language, active) VALUES('{$name}','{$name}','{$value}','{$type}', '{$lang}', 1)";
            $DB->Query($query);
        }
    }

    return true;
}
