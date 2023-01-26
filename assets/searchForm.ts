import * as $ from 'jquery';
import TomSelect from 'tom-select/dist/esm/tom-select';

export default function initSearchFormHandlers() {
    const tomSelect = new TomSelect('#searchText', {
        create: true,
        delimiter: ';',
        valueField: 'value',
        labelField: 'title',
        searchField: 'title',
        maxItems: 1,
        load: onCitySearch,
        loadThrottle: 500,
        render: {
            item: (data, escape) => {
                return '<span>' + escape(data.title) + '</span>';
            },
        },
    });

    const searchType = $('#searchType');
    searchType.on('change', () => {
        updateTomSelect(tomSelect);
    });
}

async function onCitySearch(query, callback) {
    const params = new URLSearchParams({
        q: query.trim(),
        countryCode: $('#countryCode option:selected').val() as string,
    }).toString();

    fetch('/search/cities?' + params)
        .then(res => res.json())
        .then(json => callback(json))
        .catch(callback)
    ;
}

async function onPostalSearch(query, callback) {
    const params = new URLSearchParams({
        q: query.trim(),
        countryCode: $('#countryCode option:selected').val() as string,
    }).toString();

    fetch('/search/postalCodes?' + params)
        .then(res => res.json())
        .then(json => callback(json))
        .catch(callback)
    ;
}

function updateTomSelect(tomSelect: TomSelect) {
    let searchByCity = $('#searchType option:selected').val() === 'city';

    tomSelect.clear();
    tomSelect.clearOptions();

    if (searchByCity) {
        tomSelect.settings.load = onCitySearch;
        tomSelect.settings.placeholder = 'City, State';
    } else {
        tomSelect.settings.load = onPostalSearch;
        tomSelect.settings.placeholder = 'Postal Code';
    }
    tomSelect.inputState();
    tomSelect.setup();
}
