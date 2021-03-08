<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Simplecomp extends CBitrixComponent
{
    /**
     * ID модуля инфоблоков
     */
    const IBLOCK_MODULE_ID = 'iblock';

    /**
     * Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {

        global $USER, $APPLICATION;

        try {
            if (!$USER->IsAuthorized()) {
                $APPLICATION->AuthForm(GetMessage('SIMPLECOMP_EXAM2_USER_IS_NOT_AUTHORISED'));
                return;
            }

            if (!Loader::includeModule(self::IBLOCK_MODULE_ID)) {
                ShowError(Loc::getMessage("SIMPLECOMP_EXAM2_IBLOCK_MODULE_NONE"));
                return;
            }

            if (!intval($this->arParams["PRODUCTS_IBLOCK_ID"]) > 0) {
                ShowError(Loc::getMessage("SIMPLECOMP_EXAM2_IBLOCK_ID_IS_EMPTY"));
                return;
            }

            if (!trim($this->arParams['AUTHOR_TYPE'])) {
                ShowError(Loc::getMessage('SIMPLECOMP_EXAM2_AUTHOR_TYPE_IS_EMPTY'));
            }

            if (!trim($this->arParams['AUTHOR_PROPERTY'])) {
                ShowError(Loc::getMessage('SIMPLECOMP_EXAM2_AUTHOR_PROPERTY_IS_EMPTY'));
            }


            if ($this->startResultCache(false, $USER->GetID())) {
                $currentUser = $this->getCurrentUserInfo();

                if (!$currentUser) {
                    throw new Exception(Loc::getMessage('SIMPLECOMP_EXAM2_USER_IS_NOT_FOUND'));
                }

                $arLogin = [];
                $arAuthors = $this->getAuthors($currentUser, $arLogin);

                $arThisUser = [];
                $arElements = $this->getElements($arAuthors, $arThisUser);

                $this->updateElementsForTemplate($arElements, $arThisUser, $arLogin);

                $this->includeComponentTemplate();
            }

            $count = $this->getNewsCount();
            $APPLICATION->setTitle(Loc::getMessage('SIMPLECOMP_EXAM2_PAGE_TITLE', ['#COUNT#' => $count]));

        } catch (Exception $ex) {
            AddMessage2Log($ex);
            $this->abortResultCache();
        }
    }

    /**
     * Информация о текущем пользователе
     *
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getCurrentUserInfo(): array
    {

        global $USER;

        $dbUser = UserTable::getList([
            'filter' => [
                'ID' => $USER->GetID()
            ],
            'select' => [
                $this->arParams['AUTHOR_TYPE']
            ]
        ]);

        return $dbUser->fetch();
    }

    /**
     * Получение соавторов пользователя
     *
     * @param array $arUser
     * @param array $arUsersLogin
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getAuthors(array $arUser, array &$arUsersLogin): array
    {

        global $USER;

        $arUsersID = [];

        $authorType = $arUser[$this->arParams['AUTHOR_TYPE']];

        $arFilter = [
            $this->arParams['AUTHOR_TYPE'] => $authorType,
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
                'IBLOCK_ID' => $this->arParams["PRODUCTS_IBLOCK_ID"],
                'PROPERTY_' . $this->arParams['AUTHOR_PROPERTY'] => $arUsers['ID']
            ];
            $arUsersLogin[$arUsers['ID']] = $arUsers['LOGIN'];
        }

        $arUsersID[] = [
            'IBLOCK_ID' => $this->arParams["PRODUCTS_IBLOCK_ID"],
            'PROPERTY_' . $this->arParams['AUTHOR_PROPERTY'] => $USER->GetID()
        ];

        return $arUsersID;
    }

    /**
     * Получение элементов новостей для пользователя
     *
     * @param array $arUsersID
     * @param array $arThisUser
     * @return array
     */
    protected function getElements(array $arUsersID, array &$arThisUser): array
    {

        global $USER;

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

        $arElements = [];
        while ($arIblockRes = $dbIblockRes->Fetch()) {

            $userID = $arIblockRes['PROPERTY_' . $this->arParams['AUTHOR_PROPERTY'] . '_VALUE'];

            if ($userID == $USER->GetID()) {
                $arThisUser[] = $arIblockRes['ID'];
            } else {
                $arElements[$userID]['VALUE'][] = $arIblockRes;
            }
        }

        return $arElements;
    }

    /**
     * Исключение из результирующего массива новостей, автором которых
     * является текущий пользователь и запись данных в массив arResult
     *
     * @param array $arElements
     * @param array $arThisUser
     * @param array $arUsersLogin
     */
    protected function updateElementsForTemplate(array $arElements, array $arThisUser, array $arUsersLogin): void
    {
        foreach ($arElements as $key => $elements) {
            $this->arResult['ELEMENTS'][$key]['VALUE'] = array_filter($elements['VALUE'], function ($var) use ($arThisUser) {
                return !in_array($var['ID'], $arThisUser);
            });
            $this->arResult['ELEMENTS'][$key]['LOGIN'] = $arUsersLogin[$key];
        }
    }

    /**
     * Подсчет количества новостей
     *
     * @return int
     */
    protected function getNewsCount(): int
    {
        $count = 0;

        foreach ($this->arResult['ELEMENTS'] as $key => $arElements) {
            $count += count($arElements['VALUE']);
        }

        return $count;
    }

}