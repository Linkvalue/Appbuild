function inputPackage() {
  const radioPlateform = $('input[name="plateform-app"]');
  const radioForPackage = $('input[name="plateform-app"][data-for]');
  const radioAttr = radioForPackage.attr('data-for');

  radioPlateform.on('change', (e) => {
    const $inputCurrent = $(e.currentTarget);
    if ($inputCurrent.is('[data-for]')) {
      $(`[id="${radioAttr}"]`).show();
      $(`[id="${radioAttr}"]`).find('input').attr('required', true);
    } else {
      $(`[id="${radioAttr}"]`).hide();
      $(`[id="${radioAttr}"]`).find('input').removeAttr('required');
    };
  })
};


export { inputPackage }
