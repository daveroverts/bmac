import TomSelect from 'tom-select';

/**
 * Enhance every `select[data-airport-select]` into a searchable select that
 * loads its options from the server instead of rendering every airport.
 */
document.querySelectorAll('select[data-airport-select]').forEach((el) => {
    new TomSelect(el, {
        valueField: 'value',
        labelField: 'label',
        searchField: 'label',
        maxOptions: 50,
        hidePlaceholder: true,
        load(query, callback) {
            const url = `${el.dataset.searchUrl}?q=${encodeURIComponent(query)}`;

            fetch(url, { headers: { Accept: 'application/json' } })
                .then((response) => response.json())
                .then((data) => callback(data))
                .catch(() => callback());
        },
    });
});
