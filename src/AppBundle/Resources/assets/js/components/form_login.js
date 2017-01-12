$('[data-is="form-login"]').on('change', function() {
   animationInput(this);
 });

function animationInput(form) {
  var $form = $(form);

   $form.each(function() {

     $(this).on('change', function() {
       var $formInput = $(this).find('.input_login');

       $formInput.each(function() {
         var $input = $(this);
         if($input.val().length != 0) {
           $input.addClass('active');
         } else {
           $input.removeClass('active');
         }
       });
     });
   });
};


// animation on login button if form is valid
// TODO : check if the form is valid

const $buttonLogin = $('[data-button-login]');

if ($buttonLogin.length > 0) {
  const buttonPos = $buttonLogin.offset();
  const $loader = $('<div class="loader-login"></div>');

  $loader.css('top', buttonPos.top + 'px');

  $buttonLogin.after($loader);

  $buttonLogin.click((e) => {
    $loader.addClass('valid');
  });
}
