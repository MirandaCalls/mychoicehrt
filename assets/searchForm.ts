import * as $ from "jquery";

export default function initSearchFormHandlers() {
    updateSearchTextPlaceholder();
    $('#searchType').on('change', updateSearchTextPlaceholder);
}

function updateSearchTextPlaceholder() {
    let searchByCity = $('#searchType option:selected').val() === 'city';
    let searchText = $('#searchText');
    if (searchByCity) {
        searchText.attr('placeholder', 'Example: Chicago, Illinois');
    } else {
        searchText.attr('placeholder', 'Example: 60601');
    }
}