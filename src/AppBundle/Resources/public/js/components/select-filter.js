import $ from 'jquery';

$('[data-select-filter]').each((idx, elem) => {

  const extractResultFromElem = ($el) => {
    return {
      id: $el.attr('data-id'),
      name: $el.attr('data-name'),
      label: $el.html(),
    };
  };
  const appendSelectedItem = ($el) => {
    const result = extractResultFromElem($el);
    const resultHTML = `<a class="button button_checkbox" data-select-filter-results-elem data-id="${result.id}"><input type="hidden" name="${result.name}" value="${result.id}">${result.label}</a>`;

    $elems.push(result);
    $resultElemsWrapper.append(resultHTML);

    $el.addClass('is-disabled');
    $selectElemsWrapper.removeClass('is-open');
  };
  const $elems = [];
  const $select = $(elem);
  const $inputSearch = $select.find('input[type="search"]');
  const $selectElemsWrapper = $select.find('[data-select-filter-options]');
  const $selectElems = $select.find('[data-select-filter-option]');
  const $alreadySelectedElems = $selectElems.filter('[data-selected]');
  const $results = $('[data-select-filter-results]');
  const $resultElemsWrapper = $results.find('[data-select-filter-results-elems]');
  const $deleteButton = $results.find('[data-select-filter-delete-button]');

  // Select handling
  if ($alreadySelectedElems.length) {
    $alreadySelectedElems.each((idx, elem) => {
      appendSelectedItem($(elem));
    });
  }
  $selectElems.on('click', (e) => {
    const $el = $(e.currentTarget);

    if (!$el.hasClass('is-disabled')) {
      appendSelectedItem($el);
    }
  });

  // Search handling
  $inputSearch.on('click', (e) => {
    e.stopPropagation();
    $selectElemsWrapper.toggleClass('is-open');
  });
  $(document).click(() => {
    $selectElemsWrapper.removeClass('is-open');
  });
  $inputSearch.on('input', (e) => {
    const target = $(e.target);
    const search = target.val().toLowerCase();

    $selectElemsWrapper.addClass('is-open');

    $selectElems.each((idx, elem) => {
      const text = $(elem).text().toLowerCase();
      const match = text.indexOf(search) > -1;
      $(elem).toggle(match);
    });
  });

  // Delete handling
  $deleteButton.hide();
  let $resultElemsChecked = $resultElemsWrapper.children('.is-checked');
  $results.on('click', '[data-select-filter-results-elem]', (e) => {
    $(e.currentTarget).toggleClass('is-checked');
    $resultElemsChecked = $resultElemsWrapper.children('.is-checked');
    $resultElemsChecked.length ? $deleteButton.show() : $deleteButton.hide();
  });

  $deleteButton.on('click', (e) => {
    e.preventDefault();
    $resultElemsChecked.remove();
    $deleteButton.hide();
    $resultElemsChecked.each((idx, elem) => {
      const result = extractResultFromElem($(elem));
      const i = $elems.indexOf(result);
      if ( i > -1) {
        $elems.splice(i, 1);
      }
      $selectElems.filter(`[data-id="${result.id}"]`).removeClass('is-disabled');
    });

  });

});
