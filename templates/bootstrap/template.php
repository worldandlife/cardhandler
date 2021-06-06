<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
echo '<pre>';
print_r($arResult);
echo '</pre>';
?>


<div class="status"></div>



<div id="<?= $arParams['FORM_ID'] ?>-msg"></div>

<form method="post" id="<?= $arParams['FORM_ID'] ?>">
<? foreach ($arResult['FORM_FIELDS_HIDDEN'] as $name => $value): ?>
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
<? endforeach; ?>

<? foreach ($arResult['FORM_FIELDS'] as $field): ?>
    <? if($field['TYPE'] == 'text' && $field['NAME'] == 'CARD_DEBIT' ):?>
        <div style="display: none" id="debit">
        <div class="form-group">

            <label for="<?= $field['NAME'] ?>">
                <?= $field['LABEL'] ?>:
            </label>

            <input onkeyup="this.value = this.value.replace(/[^\d]/g,'');" type="<?= $field['TYPE'] ?>"
                   class="form-control <?= $field['HTML_CLASS'] ?>"
                   id="<?= $field['NAME'] ?>"
                   name="<?= $field['NAME'] ?>"
                <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>
            >
                <button class="btn btn-danger"
                        onclick="return CardHandler.transaction()"
                >Списать</button>
            </div>
        </div>
    <? else: ?>
    <div class="form-group">
		<label for="<?= $field['NAME'] ?>">
            <?= $field['LABEL'] ?>:
        </label>

		<input onkeyup="this.value = this.value.replace(/[^\d]/g,'');" type="<?= $field['TYPE'] ?>"
			   class="form-control <?= $field['HTML_CLASS'] ?>"
			   id="<?= $field['NAME'] ?>"
			   name="<?= $field['NAME'] ?>"
               <?= $field['REQUIRED'] == 'Y' ? 'required' : '' ?>
        >
        <button class="btn btn-danger"
                onclick="return CardHandler.send()"
        >Отправить</button>
	</div>
    <? endif; ?>
<? endforeach; ?>





</form>