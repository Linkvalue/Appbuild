/**
 * Extends datatables with default options
 */
(function ($) {
    'use strict';

    // Handle default sorting classes
    var defaultSort = [0, 'asc'];
    if($('.table-advance thead .datatable-default-sort').length){
        var $sortedCol = $('.table-advance thead .datatable-default-sort').first();
        defaultSort = [$sortedCol.index(), $sortedCol.hasClass('desc') ? 'desc' : 'asc'];
    }

    // Extends default plugin options
    $.extend(true, $.fn.dataTable.defaults, {
        // Translations
        dom:
            '<"row"<"col-sm-6"f><"col-sm-6"l>>' +
            '<"row"<"col-sm-12"tr>>' +
            '<"row"<"col-sm-4"<"checkbox-handler">><"col-sm-8 text-right"ip>>',
        renderer: 'bootstrap',
        columnDefs: [{
            targets: "datatable-unsortable",
            orderable: false,
            searchable: false
        }],
        order: [ defaultSort ],
        language: {
            aria: {
                sortAscending: Translator.trans('admin.datatables.sort.ascending'),
                sortDescending: Translator.trans('admin.datatables.sort.descending')
            },
            paginate: {
                first: Translator.trans('admin.datatables.paginate.first'),
                previous: Translator.trans('admin.datatables.paginate.previous'),
                next: Translator.trans('admin.datatables.paginate.next'),
                last: Translator.trans('admin.datatables.paginate.last')
            },
            emptyTable: Translator.trans('admin.datatables.records.empty'),
            info: Translator.trans('admin.datatables.info.default'),
            infoEmpty: Translator.trans('admin.datatables.info.empty'),
            infoFiltered: Translator.trans('admin.datatables.info.filtered'),
            infoPostFix: Translator.trans('admin.datatables.info.postfix'),
            infoThousands: Translator.trans('admin.datatables.info.thousand'),
            lengthMenu: Translator.trans('admin.datatables.menu.length'),
            loadingRecords: Translator.trans('admin.datatables.records.loading'),
            processing: Translator.trans('admin.datatables.processing.label'),
            search: Translator.trans('admin.datatables.search.label'),
            zeroRecords: Translator.trans('admin.datatables.records.filtered_empty')
        }
    });

}(jQuery));
