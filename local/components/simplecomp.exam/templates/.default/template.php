<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<p><b><?=GetMessage("SIMPLECOMP_EXAM2_CAT_TITLE")?></b></p>

<?
if ($arResult['ELEMENTS']):
    foreach ($arResult['ELEMENTS'] as $key => $arElements):?>
        <div>
            [<?=$key?>] - <?=$arElements['LOGIN']?>
            <ul>
                <?foreach ($arElements['VALUE'] as $elements):?>
                    <li><?=$elements['NAME']?></li>
                <?endforeach;?>
            </ul>
        </div>
<?endforeach;
endif;?>