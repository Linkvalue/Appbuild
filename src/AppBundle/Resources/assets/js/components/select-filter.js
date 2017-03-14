function selectFilter () {
  $('.js-select').each( (idx, elem) => {

    const $select = $(elem);
    const $inputSearch = $(elem).find('input[type="search"]');
    const $selectElems = $(elem).find('.js-select__options');
    const $selectElem = $(elem).find('.js-select__option');
    const $results = $('#js-results');
    const $resultsElems = $results.find('.js-results-elems');
    const $resultsElem = $resultsElems.children();
    const $resultsElemsDelete = $results.find('.js-delete-elem');
    let $resultsElemChecked;
    const $elem = [];

  	// Hide button for deleted items
    $resultsElemsDelete.hide();

    // Push items which are already selected

    if ($resultsElems.children().length > 0) {
      $resultsElem.each( (idx, elem) => {
      	const $option = $(elem);
        const optionID = $option.attr('data-id');
        const tag = $option.html();
        const result = {id: optionID, name: tag};
        $elem.push(result);
        $selectElem.filter(`[data-id="${optionID}"]`).addClass('is-disabled');
      });
    };

    // Open the options list when you click on search input

    $inputSearch.on('click', (e) => {
      e.stopPropagation();
      $selectElems.toggleClass('is-open');
    });

    // Close the options list when you click outside the select

    $(window).click((e) => {
      $selectElems.removeClass('is-open');
    });

    // Match values when you start a search input

    $inputSearch.on('input', (e) => {
      const target = $(e.target);
      const search = target.val().toLowerCase();

      $selectElems.addClass('is-open');

      $selectElem.each( (idx, elem) => {
        const text = $(elem).text().toLowerCase();
        const match = text.indexOf(search) > -1;
        $(elem).toggle(match);
      });
    });


    // Return value on option click

    $selectElem.on('click', (e) => {
      const $option = $(e.currentTarget);

      if (!$option.hasClass('is-disabled')) {
        const optionID = $option.attr('data-id');
        const tag = $option.html();
        const result = {id: optionID, name: tag};

      	// Push the item in the table
        $elem.push(result);

        // Do appear the item
        let resultHTML = `<a class="button button_checkbox js-results-elem" data-id="${result.id}">${result.name}</a>`;
        $resultsElems.append(resultHTML);

      	// Add Class "disabled" when the item is selected
        $option.addClass('is-disabled');

        // Close the list
        $selectElems.removeClass('is-open');
      };


    });

    $results.on('click', '.js-results-elem', (e) => {

      // toggleClass 'is-checked' on click

      $(e.currentTarget).toggleClass('is-checked');
      $resultsElemChecked = $resultsElems.children('.is-checked');

      // hide or display the button 'delete user' if there is a checked button

      $resultsElemChecked.length > 0 ? $resultsElemsDelete.show() : $resultsElemsDelete.hide();
    });

    $resultsElemsDelete.on('click', (e) => {

      e.preventDefault();

      // On click button 'delete user' we remove the checked buttons and hide button 'delete user'

    	$resultsElemChecked.remove();
      $resultsElemsDelete.hide();

      // We remove the checked buttons from the array $elem which list the selected elements

      $resultsElemChecked.each( (idx, elem) => {
      	const $option = $(elem);
        const optionID = $option.attr('data-id');
        const tag = $option.html();
        const result = {id: optionID, name: tag};

        // TODO Delete result from $elem
        const i = $elem.indexOf(result);
        if ( i != -1) {
        	$elem.splice(i, 1);
        };
        // TODO Delete result from $elem


        // and we remove the class disabled
        $selectElem.filter(`[data-id="${optionID}"]`).removeClass('is-disabled');
      });

    });

  });
}

export { selectFilter }
