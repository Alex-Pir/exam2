<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
	"PARAMETERS" => array(
		"PRODUCTS_IBLOCK_ID" => array(
			"NAME" => GetMessage("SIMPLECOMP_EXAM2_CAT_IBLOCK_ID"),
			"TYPE" => "STRING",
		),
        "AUTHOR_PROPERTY" => array(
			"NAME" => GetMessage("SIMPLECOMP_EXAM2_AUTHOR_PROPERTY"),
			"TYPE" => "STRING",
		),
        "AUTHOR_TYPE" => array(
			"NAME" => GetMessage("SIMPLECOMP_EXAM2_AUTHOR_TYPE"),
			"TYPE" => "STRING",
		),
        "CACHE_TIME"  =>  Array("DEFAULT"=>36000000)
	),
);