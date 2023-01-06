import * as $ from 'jquery';
import * as leaflet from 'leaflet';
import './styles/pages/search.scss';
import initSearchFormHandlers from './searchForm';

$(() => {
   initSearchFormHandlers();

   const previewMapDiv = $('#previewMap');
   const originLatitude = previewMapDiv.data('origin-lat');
   const originLongitude = previewMapDiv.data('origin-long');

   const map = leaflet.map('previewMap');
   map.setView([originLatitude, originLongitude], 13);
   leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
   }).addTo(map);
});