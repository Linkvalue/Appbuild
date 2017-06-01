import $ from 'jquery';

const timeLoader = 900;
const $progressButtons = $('[data-load]');
const closeModal = (btn, time) => {
  const $reveal = $(btn).closest('.reveal');
  if($reveal.length) {
    setTimeout(() => {
      $reveal.foundation('close');
    }, time + timeLoader);
  }
};

$progressButtons.each((idx, elem) => {
  const $progressButton = $(elem);

  $progressButton.append('<span class="progress"><i class="icon icon-check"></i></span>');

  const $progressLine = $progressButton.find('.progress');
  const $progressLineCheck = $progressLine.find('.icon');

  $progressLine.css('transition-duration', `${timeLoader}ms`);
  $progressLineCheck.css('transition-delay', `${timeLoader + 300}ms`);

  $progressButton.click(() => {

    if ($progressButton.closest('form[data-abide]').length) {
      $progressButton.on('formvalid.zf.abide', (ev) => {
        ev.preventDefault();
        $progressButton.addClass('js-button-loading');
        closeModal($progressButton, 0);
      });
    } else {
      $progressButton.addClass('js-button-loading');
      closeModal($progressButton, timeLoader);
    }

  });

});
