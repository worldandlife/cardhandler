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
.modalDialog {
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
}
#recaptcha_render iframe {
   position: unset;
</style>

<form id="<?= $arParams['FORM_ID'] ?>" class="modalDialog pa4-l black-80" method="post">
    <div class="bg-washed-red mw7 center pa4 br2-ns ba b--black-10">
      <a href="#close" title="Закрыть" class="f4 link black-80 fr no-underline">X</a>
      <legend class="pa0 f5 f4-ns mb3 black-80"><?=$arParams['FORM_TITLE']?></legend>
      <div id="<?= $arParams['FORM_ID'] ?>-msg"></div>

      <fieldset class="cf bn ma0 pa0">
        <? foreach ($arResult['FORM_FIELDS_HIDDEN'] as $name => $value): ?>
            <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
        <? endforeach; ?>

        <? foreach ($arResult['FORM_FIELDS'] as $field): ?>
          <div class="mb3-ns mb2 cf">
            <? if ($field['TYPE'] == 'file'): ?>
              <input class="form-control f6 f5-l black-80 bg-white pa3-ns pa2 lh-solid br2"
                type="file"
                id="<?= $field['NAME'] ?>"
                name="<?= $field['NAME'] ?>"
                <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>>
            <? elseif ($field['TYPE'] == 'textarea'): ?>
          		<textarea class="form-control f6 f5-l input-reset bn black-80 bg-white pa3-ns pa2 lh-solid w-100 br2"
                id="<?= $field['NAME'] ?>"
                placeholder="<?=$field['LABEL']?>"
          			name="<?= $field['NAME'] ?>"
          			cols="30" rows="6" <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>></textarea>
          <? else: ?>
              <input class="form-control f6 f5-l input-reset bn black-80 bg-white pa3-ns pa2 lh-solid w-100 br2 <?= $field['HTML_CLASS'] ?>"
                placeholder="<?= $field['LABEL']?>"
                type="text"
                name="<?= $field['NAME'] ?>"
                value=""
                id="<?= $field['NAME'] ?>"
                <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>>
          <? endif; ?>
        </div>
        <? endforeach; ?>
      </fieldset>

      <div class="mb1 mb3-ns">Нажимая на кнопку 'Отправить', вы даете <a class="link dim gray" href="/agreement.pdf" target="_blank">согласие на обработку своих персональных данных</a></div>

      <? if ($arParams['IS_USE_CAPTCHA'] == 'Y'): ?>
				<div id="recaptcha_render" class="mb2 mb2-ns"></div>
      <? endif; ?>

			<button class="f6 f5-l button-reset pv3 tc bn bg-animate bg-black-70 hover-bg-black white pointer w-100 w-25-m w-20-l br2"
        type="submit"
        value="Отправить"
        onclick="return itdForm.send('#<?= $arParams['FORM_ID'] ?>', '<?= $arParams['ERROR_FIELD_MSG'] ?>');">Отправить</button>
  </div>
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
