// $('[data-error]').each((idx, elem) => {
//
//   $formLabel = $(elem).find('.input-wrap__label');
//   $formInput = $(elem).find('.input-wrap__input');
//
//   const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
//
// });


// $('form').on('change', (e) => {
//   validationForm(e.target);
// });
//
// function validationForm(form) {
//
//     const $form = $(form);
//     //const $elemError = $form.find('[data-required]');
//
//     $elemError.each((idx, elem) => {
//
//       const $formLabel = $(elem).find('.input-wrap__label');
//       const $formInput = $(elem).find('.input-wrap__input');
//       const $formRadio = $(elem).find("input[type='radio']:checked").length;
//       const $formError = $(elem).find('.msg-error');
//       const $formInputValue = $(elem).find('.input-wrap__input').val();
//
//       const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
//
//       if ($formInputValue.length == 0) {
//         $formLabel.addClass('js-label-error');
//         $formError.show();
//       } else {
//         $formLabel.removeClass('js-label-error');
//         $formError.hide();
//       }
//
//     });
//
//
// };
