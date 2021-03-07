<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arTemplateParameters = array(
    "CANONICAL_ID" => array(
        "NAME" => Loc::getMessage('NEWS_IBLOCK_LINK_ID'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    )
);