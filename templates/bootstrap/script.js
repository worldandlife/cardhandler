
var CardHandler = {

    send: function()
    {
        $.ajax({

            url: window.location,
            dataType: 'json',
            data: {
                method : 'getCardBalance' ,
                CARD_NUMBER : $('input[name="CARD_NUMBER"]').val(),
            },
            type : 'POST',
            error: function (response) {

                $(".status").html(response.param);

            },

            success: function (response) {

                if(response.type=='ok')
                $("#debit").show();
                else
                    $("#debit").hide();
                $(".status").html(response.type+"<br>"+response.msg);

            },

        });

        return false;
    },
    transaction: function()
    {
        $.ajax({

            url: window.location,
            dataType: 'json',
            data: {
                method : 'transaction' ,
                CARD_DEBIT : $('input[name="CARD_DEBIT"]').val(),
                CARD_NUMBER : $('input[name="CARD_NUMBER"]').val(),
            },
            type : 'POST',
            error: function (response) {

                $(".status").html(response.param);

            },

            success: function (response) {


                $(".status").html(response.type+"<br>"+response.msg);



            },

        });

        return false;
    }
};
// var falurForm = {
//     errorFieldMsg: '',
//
//     send: function(form, errorFieldMsg)
//     {
//         var $form = $(form);
//         var $msg = $(form + '-msg');
//
//         this.errorFieldMsg = errorFieldMsg;
//
//         if (falurForm.validate($form)) {
//             return false;
//         }
//
//         falurForm.sendRequest($form, $msg);
//
//         return false;
//     },
//     serializeForm: function (form)
//     {
//         if (!!window.FormData) {
//             return new FormData(document.forms[form.attr('id')]);
//         }
//
//         var data = form.serializeArray();
//
//         data.map(function (element) {
//             var $e = $('[name="'+element.name+'"]');
//
//             element.required = $e.attr('required') ? true : false;
//         });
//
//         return data;
//     },
//     validateEmail: function (email)
//     {
//         var re = /\S+@\S+\.\S+/;
//         return re.test(email);
//     },
//     validateAddError: function ($pe, $e, errorFieldMsgInput)
//     {
//         var errorFieldMsg = !errorFieldMsgInput || errorFieldMsgInput === ''
//                             ? this.errorFieldMsg
//                             : errorFieldMsgInput;
//
//         $pe.addClass('has-error');
//         if ($e.siblings('.help-block').length === 0) {
//             $e.after('<span class="help-block"><strong>'+errorFieldMsg+'</strong></span>');
//         }
//
//         $e.on('keypress', function () {
//             if ( $e.val().length > 0  ) {
//                 $pe.removeClass('has-error');
//                 $e.siblings('.help-block').remove();
//             }
//         });
//     },
//     validate: function (form)
//     {
//         notValidate = false;
//
//         form.find('.form-group input, .form-group textarea').each(function() {
//             var $e = $(this);
//             var $pe = $e.parent();
//             var isEmpty = $e.is('[required]') && '' === $e.val();
//
//             switch ($e.attr('type')) {
//                 case 'email':
//                     if (isEmpty && falurForm.validateEmail($e.val())) {
//                         falurForm.validateAddError($pe, $e);
//                         notValidate = true;
//                     }
//                     break;
//
//                 default:
//                     if (isEmpty) {
//                         falurForm.validateAddError($pe, $e);
//                         notValidate = true;
//                     }
//                     break;
//             }
//         });
//
//         return notValidate;
//     },
//     clearForm: function (form)
//     {
//         var inputs = 'input:text, input[type="file"], input[type="email"], input[type="password"], textarea';
//
//         form.find(inputs).each(function () {
//             $(this).val('');
//         });
//     },
//     sendRequest: function (form, msg, successMsg, errorMsg)
//     {
//         var tpl = '<div class="alert {{CLASS}}"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>{{MSG}}</p></div>';
//
//         $.ajax({
//             type: 'POST',
//             url: window.location,
//             dataType: 'json',
//             processData: false,
//             contentType: false,
//             data: falurForm.serializeForm(form)
//         })
//         .done(function( response ) {
//             if ('ok' === response.type) {
//                 msg.html(tpl.replace('{{MSG}}', response.msg).replace('{{CLASS}}', 'alert-success'));
//
//                 falurForm.clearForm(form);
//             } else {
//                 msg.html(tpl.replace('{{MSG}}', response.msg).replace('{{CLASS}}', 'alert-danger'));
//
//                 falurForm.refreshCaptcha(form);
//             }
//         })
//         .fail(function( jqXHR, textStatus ) {
//             console.log(jqXHR, textStatus);
//         });
//     }
// };
