$('[data-select-display]').each(function() {

  var $selectDisplay = $(this);
  var id = $(this).attr('data-toggle');
  var $selectOptions = $(`[data-select-options][id="${id}"]`);

  var $selectOptionItem = $selectOptions.children('li');

  $selectOptionItem.click(function() {
    $selectDisplay.html($(this).html());
    $selectOptions.foundation('close');

  });
});


$('[data-select-search]').each( (idx, elem) => {
  const $selectSearch = $(elem);
  const $selectSearchInput = $(elem).find('[data-select-search]');
  const $selectSearchOptions = $(elem).find('[data-select-search-results]');
  const $selectSearchOption = $selectSearchOptions.children();
  const $selectSearchLabel = $(elem).find('[data-select-search-label]');

  // Add id on each children

  $selectSearchOption.each( (idx, elem) => {
    $(elem).attr('data-id', `${idx}`);
  });

  // Open the options list when you click on search input

  $selectSearchInput.on('click', (e) => {
    e.stopPropagation();
    $selectSearchOptions.toggleClass('is-open');
  });

  // Close the options list when you click outside the select

  $(window).click((e) => {
    $selectSearchOptions.removeClass('is-open');
  });

  // $(document).on('click', (e) => {
  //   const $clicked = $(e.target);
  //
  //   if(!$clicked.parents().is('[data-select-search]')) {
  //     $selectSearchOptions.removeClass('is-open');
  //   }
  // });

  // Return value on option click

  $selectSearchOption.on('click', (e) => {
    const $option = $(e.target);
    const tag = $option.html();

    $selectSearchLabel.append(tag);
    $selectSearchOptions.removeClass('is-open');
  });

  //

  $selectSearchInput.on('input', (e) => {
    const target = $(e.target);
    const search = target.val().toLowerCase();

    $selectSearchOptions.addClass('is-open');

    $selectSearchOption.each( (idx, elem) => {
      const text = $(elem).text().toLowerCase();
      const match = text.indexOf(search) > -1;
      $(elem).toggle(match);
    });
  });

})
