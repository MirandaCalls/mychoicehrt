import * as $ from 'jquery';
import * as leaflet from 'leaflet';
import {Circle, LatLngExpression, Map, Marker} from "leaflet";
import './styles/pages/search.scss';
import initSearchFormHandlers from './searchForm';
import ClickEvent = JQuery.ClickEvent;

const METERS_IN_MILE = 1609.344;

$(() => {
   initSearchFormHandlers();

   const mapId = 'previewMap';
   const previewMapDiv = $('#' + mapId);
   const originLatitude = previewMapDiv.data('origin-latitude');
   const originLongitude = previewMapDiv.data('origin-longitude');
   const searchRadius = previewMapDiv.data('search-radius');

   const map = renderMap(mapId, originLatitude, originLongitude);
   const searchRadiusOverlay = plotSearchRadius(map, [originLatitude, originLongitude], searchRadius);
   const markers = plotClinicMarkers(map);

   const overlays = {
      'Search Radius': searchRadiusOverlay,
   };
   leaflet.control.layers({}, overlays).addTo(map);

   $('.clinic').on('click', (evt: ClickEvent) => {
      onClinicTap(evt, map, markers);
   });
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

function plotClinicMarkers(map: Map): MarkersHashMap {
   const icon = leaflet.icon({
      iconUrl: '/build/images/marker-icon.png',
   });

   const markers: MarkersHashMap = {};
   const markerCoords: LatLngExpression[] = [];
   $('.clinic').each((index: number, ele: HTMLElement) => {
      const clinic = $(ele);
      const clinicIndex = clinic.data('index');
      const latitude = clinic.data('latitude');
      const longitude = clinic.data('longitude');

      markerCoords.push([latitude, longitude]);

      const marker = leaflet.marker([latitude, longitude], {icon: icon});
      marker.addTo(map);
      markers[clinicIndex] = marker;
   });

   map.fitBounds(leaflet.latLngBounds(markerCoords));
   return markers;
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

function onClinicTap(evt: ClickEvent, map: Map, markers: MarkersHashMap) {
   const clinic = $(evt.currentTarget);
   const clinicIndex = clinic.data('index');

   const marker = markers[clinicIndex];
   map.flyTo(marker.getLatLng(), 16);
}

interface MarkersHashMap {
   [index: string]: Marker;
}