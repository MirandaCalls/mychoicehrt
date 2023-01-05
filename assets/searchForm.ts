import * as $ from "jquery";

export default function initSearchFormHandlers() {
    updateSearchTextPlaceholder();
    $("input[name='searchType']").on('change', updateSearchTextPlaceholder);
}

function updateSearchTextPlaceholder() {
    let searchByCity = $("input[name='searchType'][value='city']").prop("checked");
    let searchText = $('#searchText');
    if (searchByCity) {
        searchText.attr('placeholder', 'Example: Chicago, Illinois');
    } else {
        searchText.attr('placeholder', 'Example: 60601')
    }
}