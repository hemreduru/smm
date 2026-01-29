/**
 * DataTable Module
 *
 * Provides global DataTable configuration, localization, and helper functions.
 * All DataTable instances should use initDataTable() for consistent behavior.
 */

/**
 * DataTable language configurations (inline to avoid CDN)
 */
const languages = {
    tr: {
        processing: '<span class="spinner-border spinner-border-sm align-middle me-2"></span> İşleniyor...',
        search: 'Ara:',
        lengthMenu: '_MENU_ kayıt göster',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: 'Kayıt bulunamadı',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        infoPostFix: '',
        loadingRecords: 'Yükleniyor...',
        zeroRecords: 'Eşleşen kayıt bulunamadı',
        emptyTable: 'Tabloda veri yok',
        paginate: {
            first: '<i class="bi bi-chevron-double-left"></i>',
            previous: '<i class="bi bi-chevron-left"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>'
        },
        aria: {
            sortAscending: ': artan sırala',
            sortDescending: ': azalan sırala'
        },
        select: {
            rows: {
                _: '%d kayıt seçildi',
                0: '',
                1: '1 kayıt seçildi'
            }
        }
    },
    en: {
        processing: '<span class="spinner-border spinner-border-sm align-middle me-2"></span> Processing...',
        search: 'Search:',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'No entries found',
        infoFiltered: '(filtered from _MAX_ total entries)',
        infoPostFix: '',
        loadingRecords: 'Loading...',
        zeroRecords: 'No matching records found',
        emptyTable: 'No data available in table',
        paginate: {
            first: '<i class="bi bi-chevron-double-left"></i>',
            previous: '<i class="bi bi-chevron-left"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>'
        },
        aria: {
            sortAscending: ': activate to sort ascending',
            sortDescending: ': activate to sort descending'
        },
        select: {
            rows: {
                _: '%d rows selected',
                0: '',
                1: '1 row selected'
            }
        }
    }
};

/**
 * Get current locale from HTML lang attribute
 */
function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'en';
    return htmlLang.substring(0, 2); // 'tr' or 'en'
}

/**
 * Get language configuration for current locale
 */
function getLanguageConfig() {
    const locale = getCurrentLocale();
    return languages[locale] || languages.en;
}

/**
 * Default DataTable configuration
 * Metronic-compatible DOM structure for proper styling
 */
const defaultConfig = {
    responsive: true,
    processing: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    // Metronic-compatible DOM layout
    dom: `<'row align-items-center'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'l><'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'f>>
          <'table-responsive'tr>
          <'row align-items-center'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i><'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>`,
    // Remove default search box (we use custom search inputs)
    searching: true,
    // Order configuration
    order: [],
    // Stateful - remember page, length, order
    stateSave: false,
};

/**
 * Initialize a DataTable with global defaults and localization
 *
 * @param {string|jQuery} selector - Table selector or jQuery object
 * @param {Object} options - DataTable options (merged with defaults)
 * @returns {DataTable} - DataTable instance
 *
 * @example
 * // Basic server-side DataTable
 * const table = initDataTable('#users-table', {
 *     serverSide: true,
 *     ajax: {
 *         url: '/users/data',
 *         data: function(d) {
 *             d.role_id = $('#role-filter').val();
 *         }
 *     },
 *     columns: [
 *         { data: 'name', name: 'name' },
 *         { data: 'email', name: 'email' },
 *         { data: 'actions', orderable: false, searchable: false }
 *     ],
 *     order: [[0, 'asc']]
 * });
 *
 * @example
 * // Client-side DataTable with custom DOM (no search/length controls)
 * const table = initDataTable('#simple-table', {
 *     dom: 'rt<"row"<"col-md-6"i><"col-md-6"p>>',
 *     columns: [...]
 * });
 */
export function initDataTable(selector, options = {}) {
    const $table = typeof selector === 'string' ? $(selector) : selector;

    if (!$table.length) {
        console.warn(`DataTable: Element not found for selector "${selector}"`);
        return null;
    }

    // Merge configurations: defaults -> options
    const config = {
        ...defaultConfig,
        language: getLanguageConfig(),
        ...options
    };

    // Add default error handler if ajax is present and no error handler defined
    if (config.ajax && typeof config.ajax === 'object' && !config.ajax.error) {
        config.ajax.error = function(xhr, error, thrown) {
            console.error('DataTables Ajax Error:', error, thrown);
            if (typeof window.showError === 'function') {
                const locale = getCurrentLocale();
                const errorMsg = locale === 'tr'
                    ? 'Veri yüklenirken hata oluştu. Lütfen tekrar deneyin.'
                    : 'Error loading data. Please try again.';
                window.showError(errorMsg);
            }
        };
    }

    // Initialize DataTable
    const table = $table.DataTable(config);

    return table;
}

/**
 * Bind a search input to a DataTable
 *
 * @param {DataTable} table - DataTable instance
 * @param {string} inputSelector - Search input selector
 * @param {number} delay - Debounce delay in ms (default: 300)
 */
export function bindSearch(table, inputSelector, delay = 300) {
    const $input = $(inputSelector);
    if (!$input.length || !table) return;

    let timeout;
    $input.on('keyup', function() {
        clearTimeout(timeout);
        const value = this.value;
        timeout = setTimeout(() => {
            table.search(value).draw();
        }, delay);
    });
}

/**
 * Bind a filter dropdown/input to trigger table reload
 *
 * @param {DataTable} table - DataTable instance
 * @param {string} buttonSelector - Apply filter button selector
 */
export function bindFilterButton(table, buttonSelector) {
    const $button = $(buttonSelector);
    if (!$button.length || !table) return;

    $button.on('click', function() {
        table.ajax.reload();
    });
}

/**
 * Bind multiple filter controls to reload table on change
 *
 * @param {DataTable} table - DataTable instance
 * @param {string|Array} selectors - Filter control selector(s)
 */
export function bindFilters(table, selectors) {
    if (!table) return;

    const selectorArray = Array.isArray(selectors) ? selectors : [selectors];

    selectorArray.forEach(selector => {
        $(selector).on('change', function() {
            table.ajax.reload();
        });
    });
}

/**
 * Reload DataTable data
 *
 * @param {DataTable} table - DataTable instance
 * @param {boolean} resetPaging - Reset to first page (default: false)
 */
export function reloadTable(table, resetPaging = false) {
    if (table) {
        table.ajax.reload(null, resetPaging);
    }
}

/**
 * Destroy DataTable instance
 *
 * @param {DataTable} table - DataTable instance
 */
export function destroyTable(table) {
    if (table) {
        table.destroy();
    }
}

// Export language configs for custom use
export { languages, getLanguageConfig, getCurrentLocale };

// Make initDataTable globally available
window.initDataTable = initDataTable;
window.bindSearch = bindSearch;
window.bindFilterButton = bindFilterButton;
window.bindFilters = bindFilters;
window.reloadTable = reloadTable;
