$('[data-is="form-login"]').on('change', (e) => {
  animationInput(e.target);
 });

function animationInput(form) {

    var $formInput = $(form);

    if ($formInput.val().length != 0) {
      $formInput.addClass('active');
    } else {
      $formInput.removeClass('active');
    };

};


// animation on login button if form is valid
// TODO : check if the form is valid

// const $buttonLogin = $('[data-button-login]');
//
// if ($buttonLogin.length > 0) {
//   const buttonPos = $buttonLogin.offset();
//   const $loader = $('<div class="loader-login"></div>');
//
//   $loader.css('top', buttonPos.top + 'px');
//
//   $buttonLogin.after($loader);
//
//   $buttonLogin.click((e) => {
//     $loader.addClass('valid');
//   });
// }
