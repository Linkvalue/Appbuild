$('[data-is="form-login"]').on('change', (e) => {
  animationInput(e.target);
 });

function animationInput(form) {

    const $formInput = $(form);

    if ($formInput.val().length != 0) {
      $formInput.addClass('active');
    } else {
      $formInput.removeClass('active');
    };

};
