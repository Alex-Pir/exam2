<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ($arParams['CANONICAL_ID']) {
    $dbElem = CIBlockElement::GetList(
        array(),
        array(
            'IBLOCK_ID' => (int)$arParams['CANONICAL_ID'],
            'PROPERTY_CANONICAL_NEWS' => $arResult['ID']
        ),
        false,
        false,
        array(
            'NAME'));
    if ($arElem = $dbElem->GetNext()) {

        $arResult['CANONICAL_NEWS'] = $arElem['NAME'];

        $this->__component->setResultCacheKeys(array(
            'CANONICAL_NEWS'
        ));

    }
}