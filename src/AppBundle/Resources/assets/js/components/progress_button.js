// Animation on button "supprimer" that you can find on builds page when you delete a build

function closeModal(btn, time) {
  if($(btn).closest('.reveal').length > 0) {
    const $reveal = $(btn).closest('.reveal');
    setTimeout(() => {
      $reveal.foundation('close');
    }, time + 900);
  };
};

function buttonAnimation() {

  const $progressButtons = $('[data-load]');

  $progressButtons.each((idx, elem) => {
    const $progressButton = $(elem);
    const timeLoader = 900;

    $progressButton
      .append('<span class="progress"><i class="icon icon-check"></i></span>');

    const $progressLine = $progressButton.find('.progress');
    const $progressLineCheck = $progressLine.find('.icon');

    $progressLine.css('transition-duration', `${timeLoader}ms`);
    $progressLineCheck.css('transition-delay', `${timeLoader + 300}ms`);

    $progressButton.click( () => {

      if ($progressButton.closest('form[data-abide]').length > 0) {
        const $form = $progressButton.closest('form[data-abide]');

        $progressButton.on("formvalid.zf.abide", function(ev, frm) {
          ev.preventDefault();
          $progressButton
            .addClass('js-button-loading');

          closeModal($progressButton);
        });

      } else {
        $progressButton
          .addClass('js-button-loading');

        closeModal($progressButton, timeLoader);
      };

    });

  });
}



export { buttonAnimation }
