import $ from 'jquery';

const $formLogin = $('[data-is="form-login"]');
const animationInput = ($formInput) => {
  if ($formInput.val().length != 0) {
    $formInput.addClass('active');
  } else {
    $formInput.removeClass('active');
  }
};

if ($formLogin.length) {
  animationInput($formLogin.find('input'));

  $formLogin.on('change', (e) => {
    animationInput($(e.target));
  });
}
