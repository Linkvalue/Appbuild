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

  $progressButton.click(() => {
    $progressButton
      .addClass('js-button-loading');

    if($progressButton.closest('.reveal').length > 0) {
      const $reveal = $progressButton.closest('.reveal');
      setTimeout(() => {
        $reveal.foundation('close');
      }, timeLoader + 900);
    };
  });

  //setTimeout(() => $progressButton.removeClass('js-button-loading'), timeLoader);

});
