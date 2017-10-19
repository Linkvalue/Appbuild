import $ from 'jquery';

const dataAttrName = 'data-required-when-checked';
const requireWhenChecked = ($el) => {
  ($el.prop('checked')) ? $($el.attr(dataAttrName)).attr('required', 'required') : $($el.attr(dataAttrName)).removeAttr('required');
};

$(`input[${dataAttrName}]`).each((idx, elem) => {
  const $input = $(elem);
  const $inputsWithSameName = $(`input[name="${$input.attr('name')}"]`);

  // input init
  requireWhenChecked($input);

  // watch changes on inputs with same name
  $inputsWithSameName.on('change', () => {
    requireWhenChecked($input);
  });
});
