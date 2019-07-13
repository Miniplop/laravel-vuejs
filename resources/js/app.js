import store from './store'
import router from './router'
import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import { LMap, LTileLayer, LMarker, LPolyline } from 'vue2-leaflet';
import { Icon } from 'leaflet'
import 'leaflet/dist/leaflet.css'
import Vue2LeafletMarkerCluster from 'vue2-leaflet-markercluster'

Vue.component('l-map', LMap);
Vue.component('l-tile-layer', LTileLayer);
Vue.component('l-marker', LMarker);
Vue.component('l-polyline', LPolyline);

delete Icon.Default.prototype._getIconUrl;

Icon.Default.mergeOptions({
  iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
  iconUrl: require('leaflet/dist/images/marker-icon.png'),
  shadowUrl: require('leaflet/dist/images/marker-shadow.png')
});

import App from './App.vue'

Vue.use(VueAxios, axios)

Vue.component('v-marker-cluster', Vue2LeafletMarkerCluster)

const app = new Vue({
    el: '#app',
    store,
    router,
    template: `<App />`,
    components: { App }
});
