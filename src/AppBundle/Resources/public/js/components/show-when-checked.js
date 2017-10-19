import $ from 'jquery';

const dataAttrName = 'data-show-when-checked';
const showWhenChecked = ($el) => {
  ($el.prop('checked')) ? $($el.attr(dataAttrName)).show() : $($el.attr(dataAttrName)).hide();
};

$(`input[${dataAttrName}]`).each((idx, elem) => {
  const $input = $(elem);
  const $inputsWithSameName = $(`input[name="${$input.attr('name')}"]`);

  // input init
  showWhenChecked($input);

  // watch changes on inputs with same name
  $inputsWithSameName.on('change', () => {
    showWhenChecked($input);
  });
});
