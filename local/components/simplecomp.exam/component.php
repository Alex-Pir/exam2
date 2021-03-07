<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Iblock;
use Bitrix\Main\UserTable;

if(!Loader::includeModule("iblock"))
{
	ShowError(GetMessage("SIMPLECOMP_EXAM2_IBLOCK_MODULE_NONE"));
	return;
}

if(!intval($arParams["PRODUCTS_IBLOCK_ID"]) > 0)
{
    ShowError(GetMessage("SIMPLECOMP_EXAM2_IBLOCK_ID_IS_EMPTY"));
    return;
}

if (!trim($arParams['AUTHOR_TYPE'])) {
    ShowError(GetMessage('SIMPLECOMP_EXAM2_AUTHOR_TYPE_IS_EMPTY'));
}

if (!trim($arParams['AUTHOR_PROPERTY'])) {
    ShowError(GetMessage('SIMPLECOMP_EXAM2_AUTHOR_PROPERTY_IS_EMPTY'));
}

global $USER, $APPLICATION;

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm(GetMessage('SIMPLECOMP_EXAM2_USER_IS_NOT_AUTHORISED'));
    return;
}

if ($this->StartResultCache(false, $USER->GetID())) {
    $dbUser = UserTable::getList([
        'filter' => [
            'ID' => $USER->GetID()
        ],
        'select' => [
            $arParams['AUTHOR_TYPE']
        ]
    ]);

    if ($arUser = $dbUser->fetch()) {

        $arUsersID = [];
        $arUsersLogin = [];

        $authorType = $arUser[$arParams['AUTHOR_TYPE']];

        $arFilter = [
            $arParams['AUTHOR_TYPE'] => $authorType,
            '!ID' => $USER->GetID()
        ];

        $arSelect = [
            'ID', 'LOGIN'
        ];

        $dbUsers = UserTable::getList([
            'filter' => $arFilter,
            'select' => $arSelect
        ]);

        while ($arUsers = $dbUsers->fetch()) {
            $arUsersID[] = [
                'IBLOCK_ID' => $arParams["PRODUCTS_IBLOCK_ID"],
                'PROPERTY_' . $arParams['AUTHOR_PROPERTY'] => $arUsers['ID']
            ];
            $arUsersLogin[$arUsers['ID']] = $arUsers['LOGIN'];
        }

        $arUsersID[] = [
            'IBLOCK_ID' => $arParams["PRODUCTS_IBLOCK_ID"],
            'PROPERTY_' . $arParams['AUTHOR_PROPERTY'] => $USER->GetID()
        ];

        $arIBlockFilter = [
            'LOGIC' => 'OR'
        ];

        $arIBlockFilter = array_merge($arIBlockFilter, $arUsersID);

        $dbIblockRes = CIBlockElement::GetList(
            [],
            $arIBlockFilter,
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_AUTHOR']
        );

        $arThisUser = [];
        $arElements = [];
        while ($arIblockRes = $dbIblockRes->Fetch()) {

            $userID = $arIblockRes['PROPERTY_' . $arParams['AUTHOR_PROPERTY'] . '_VALUE'];

            if ($userID == $USER->GetID()) {
                $arThisUser[] = $arIblockRes['ID'];
            } else {
                $arElements[$userID]['VALUE'][] = $arIblockRes;
            }
        }

        foreach ($arElements as $key => $elements) {
            $arResult['ELEMENTS'][$key]['VALUE'] = array_filter($elements['VALUE'], function($var) use ($arThisUser) {
                return !in_array($var['ID'], $arThisUser);
            });
            $arResult['ELEMENTS'][$key]['LOGIN'] = $arUsersLogin[$key];
        }

    }

    $this->includeComponentTemplate();
}
?>