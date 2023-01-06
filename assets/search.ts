import * as $ from 'jquery';
import * as leaflet from 'leaflet';
import {Map} from "leaflet";
import './styles/pages/search.scss';
import initSearchFormHandlers from './searchForm';

$(() => {
   initSearchFormHandlers();

   const previewMapDiv = $('#previewMap');
   const originLatitude = previewMapDiv.data('origin-latitude');
   const originLongitude = previewMapDiv.data('origin-longitude');

   const map = leaflet.map('previewMap');
   map.setView([originLatitude, originLongitude], 13);
   leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
   }).addTo(map);

   plotClinics(map);
});

function plotClinics(map: Map) {
   const icon = leaflet.icon({
      iconUrl: '/build/images/marker-icon.png',
   });

   const clinics = $('.clinic');
   clinics.each((index: number, ele: HTMLElement) => {
      const clinic = $(ele);
      const latitude = clinic.data('latitude');
      const longitude = clinic.data('longitude');

      leaflet.marker([latitude, longitude], {icon: icon}).addTo(map);
   });
}