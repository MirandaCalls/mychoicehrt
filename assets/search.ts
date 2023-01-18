import * as $ from 'jquery';
import * as leaflet from 'leaflet';
import {Circle, LatLngExpression, Map} from 'leaflet';
import * as bootstrap from 'bootstrap';
import './styles/pages/search.scss';
import initSearchFormHandlers from './searchForm';

const METERS_IN_MILE = 1609.344;

$(() => {
   initSearchFormHandlers();

   const tooltipTriggerList = $('[data-bs-toggle="tooltip"]');
   tooltipTriggerList.each((index, tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));

   const mapId = 'previewMap';
   const previewMapDiv = $('#' + mapId);
   const originLatitude = previewMapDiv.data('origin-latitude');
   const originLongitude = previewMapDiv.data('origin-longitude');
   const searchRadius = previewMapDiv.data('search-radius');

   const map = renderMap(mapId, originLatitude, originLongitude);
   const searchRadiusOverlay = plotSearchRadius(map, [originLatitude, originLongitude], searchRadius);
   plotClinicMarkers(map);

   const overlays = {
      'Search Radius': searchRadiusOverlay,
   };
   leaflet.control.layers({}, overlays).addTo(map);
});

function renderMap(mapId: string, centerLatitude: number, centerLongitude: number): Map {
   const map = leaflet.map(mapId);
   map.setView([centerLatitude, centerLongitude], 13);
   leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 20,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
   }).addTo(map);
   return map;
}

function plotClinicMarkers(map: Map) {
   const icon = leaflet.icon({
      iconUrl: '/build/images/marker-icon.png',
      popupAnchor: [12, 0],
   });

   const markerCoords: LatLngExpression[] = [];
   $('.clinic').each((index: number, ele: HTMLElement) => {
      const clinic = $(ele);
      const latitude = clinic.data('latitude');
      const longitude = clinic.data('longitude');
      const name = clinic.find('.clinic-name').text();

      markerCoords.push([latitude, longitude]);

      const marker = leaflet.marker([latitude, longitude], {
         icon: icon,
         title: name,
      });
      marker.bindPopup(name);

      marker.on('click', () => {
         marker.openPopup();
      });

      marker.addTo(map);
   });

   map.fitBounds(leaflet.latLngBounds(markerCoords));
}

function plotSearchRadius(map: Map, coordinate: LatLngExpression, searchRadius: number): Circle {
   const circle = leaflet.circle(coordinate, {
      radius: searchRadius * METERS_IN_MILE,
      fillOpacity: 0.10,
   });
   const layer = circle.addTo(map);
   layer.addTo(map);
   return circle;
}
