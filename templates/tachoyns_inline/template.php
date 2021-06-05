<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
if ($APPLICATION->GetShowIncludeAreas()) {
	echo '<button class="pa3 bg-gray white">Форма '.$arParams['FORM_ID'].'</button>';
}

/**
 * Компонент форм для Bitrix
 *
 * @author    Maxim Gerzon <gerzon@it-delta.ru>
 * @link      https://github.com/it-delta/feedback_form
 * @copyright 2020 it-delta.ru
 * @license   GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<style type="text/css">
/*.modalDialog {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	background: rgba(0,0,0,0.8);
	z-index: 99999;
	transition: all 500ms ease-in;
	display: none;
	pointer-events: none;
}
.modalDialog:target {
	display: block;
	pointer-events: auto;
}*/
.error-field_border{
    border:  1px solid #ed1f23 !important;
}
.send-form{
    margin: 10px 0 20px auto;
}
#recaptcha_render iframe {
    position: unset;
}
</style>


<form id="<?= $arParams['FORM_ID'] ?>" class="feedback_form" name="iblock_add" action="" method="post" enctype="multipart/form-data" style="display: none">
    <div class="feedback_form-title"><?=$arParams['FORM_TITLE']?></div>
    <input type="hidden" name="sessid" id="sessid" value="4bdc2fd8c4d69c039b9d0474abdb6eb7">
    <? foreach ($arResult['FORM_FIELDS_HIDDEN'] as $name => $value): ?>
        <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
    <? endforeach; ?>
    <?if(!empty($arParams['PRODUCT_ID'])):?>
        <input type="hidden" name="PRODUCT" value="<?=$arParams['PRODUCT_ID']?>">
    <?endif;?>
    <div id="<?= $arParams['FORM_ID'] ?>-msg"></div>
    <div class="row">
        <? foreach ($arResult['FORM_FIELDS'] as $field): ?>
                <? if ($field['TYPE'] == 'file'): ?>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <input class="form-control"
                           type="file"
                           id="<?= $field['NAME'] ?>"
                           name="<?= $field['NAME'] ?>"
                        <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>>
                </div>
                <? elseif ($field['TYPE'] == 'textarea'): ?>
                <div class="col-12"><br>
                <div class="html_box">
                    <textarea class="form-control"
                              id="<?= $field['NAME'] ?>"
                              placeholder="<?=$field['LABEL']?>"
                              name="<?= $field['NAME'] ?>"
                              cols="30" rows="6" <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>></textarea>
                </div>
                </div>
                <? else: ?>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <input class="form-control <?= $field['HTML_CLASS'] ?>"
                           placeholder="<?= $field['LABEL']?>"
                           type="text"
                           name="<?= $field['NAME'] ?>"
                           value=""
                           id="<?= $field['ID'] ? $field['ID'] :  $field['NAME']?>"
                        <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>>
                </div>
                <? endif; ?>

        <? endforeach; ?>
        <div class="col-12">
            <label class="checkbox-inline">
                <input type="checkbox" value="" required id="feed_agree" class="politic_checkbox" checked="checked"> Подтверждаю <a href="/soglasie.pdf" class="confirm_link" target="_blank">согласие на обработку персональных данных</a>
            </label>
        </div>
    </div>

    <? if ($arParams['IS_USE_CAPTCHA'] == 'Y'): ?>
        <div id="recaptcha_render" class="mb2 mb2-ns"></div>
    <? endif; ?>

    <button class="btn_site send-form"
            type="submit"
            value="Отправить"
            onclick="">Отправить</button>
</form>

<? if ($arParams['IS_USE_CAPTCHA'] == 'Y'): ?>

    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script>
        var recaptchaWidget1;
        var onloadCallback = function () {
            recaptchaWidget1 = grecaptcha.render('recaptcha_render', { sitekey : '<?= $arParams['CAPTCHA_SITE_KEY'] ?>' });
        };
    </script>

<? endif; ?>
<script>
    $(document).on("click", ".btn_site", function(e){
        if($("#<?=$arParams['FORM_ID'] ?> #feed_agree").is(":checked")){
            return itdForm.send('#<?= $arParams['FORM_ID'] ?>', '<?= $arParams['ERROR_FIELD_MSG'] ?>');
        } else {
            e.preventDefault();
            alert('Подтвердите согласие на обработку персональных данных!');
            return false;
        }
    });

    $(document).ready(function(){
        $("#feed_phone").mask("+7(999) 999-9999");
    });
</script>