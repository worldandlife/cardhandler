/**
 * Bitrix component form (webgsite.ru)
 * Компонент для битрикс, создание форм
 *
 * @author    Falur <ienakaev@ya.ru>
 * @link      https://github.com/falur/bitrix.com.form
 * @copyright 2015 - 2016 webgsite.ru
 * @license   GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
 */

var itdForm = {
  send: function (formId, errorFieldMsg)
  {
    const form = $(formId);
    const msg = $(formId + '-msg');
    msg.html('');

    if (itdForm.validate(form, errorFieldMsg)) {
      return false;
    }

    itdForm.sendRequest(form, msg);

    return false;
  },

  serializeForm: function (form)
  {
    const data = form.serializeArray();

    data.map(function (element) {
      const e = $('[name="' + element.name + '"]');
      element.required = e.attr('required');
    });
    return data;
  },

  validate: function (form, errorFieldMsg) {
    let notValidate = false;

    form.find('.form-control').each(function () {
      const element = $(this);

      if ('required' === element.attr('required') && '' === element.val().trim()) {
        if (element.siblings('.has-error').length === 0) {
          element.addClass('error-field_border');
          element.after('<div class="has-error f6 red mt2">' + errorFieldMsg + '</div>');
        }

        element.on('keyup', function() {
          if (element.val().trim() !== '') {
            element.siblings('.has-error').remove();
            element.removeClass('error-field_border');
          }
        });

        notValidate = true;
      }
    });
    return notValidate;
  },

  clearForm: function (form) {
    form.find('input:text, input[type="email"], input[type="password"], textarea').each(function () {
      $(this).val('');
    });
  },

  sendRequest: function (form, msg)
  {
    const tpl = '<div class="alert {{CLASS}}"><button type="button" class="close1" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>{{MSG}}</p></div>';

    $.ajax({
      type: 'POST',
      url: window.location,
      dataType: 'json',
      data: itdForm.serializeForm(form)
    }).done(function (response) {
      if (response.type === 'ok') {
        msg.html(`<div class="mb2 green">${response.msg}</div>`);
        itdForm.clearForm(form);
      } else {
        msg.html(`<div class="mb2 red">${response.msg}</div>`);
      }
    }).fail(function (jqXHR, textStatus) {
      console.log(jqXHR, textStatus);
    });
  }
};
