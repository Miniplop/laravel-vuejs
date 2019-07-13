<template>
    <div id="app">
        <header class="header">
            <div class="header-left-container">
                <img class="header-image" height="64" src="images/logo2.png"/>
                City Manager
            </div>
            <div @click="resetSelection()" class="reset">Reset selection</div>
        </header>
        <div class="map-container">
            <div class="tasker-list" >
                <div :class="[item === selectedTasker ?  'selected' : '']" @click="onClickTasker(item)" class="list-item" v-for="(item) in taskers">
                    Tasker n°{{item}}
                </div>
            </div>
            <LMap
                :zoom="zoom"
                :center="center"
                ref="map"
            >
                <LTileLayer :url="url"></LTileLayer>
                <v-marker-cluster>
                    <LMarker v-for="task in filteredTasks" :lat-lng="[task.lat, task.lng]" >
                        <LTooltip>{{task.dueTime}}</LTooltip>
                    </LMarker>
                </v-marker-cluster>
                <LPolyline v-for="line in lines" :lat-lngs="line"></LPolyline>
            </LMap>
        </div>

    </div>
</template>

<script>
  import {LMap, LTileLayer, LMarker, LPolyline, LTooltip } from 'vue2-leaflet';
  import test from './data.json';

  export default {
        name: 'app',
        data() {
          return {
            url: 'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
            zoom: 14,
            center: [48.8566, 2.3522],
            taskers: test.taskersCount,
            tasks: test.tasks,
            selectedTasker: null
          }
        },
        mounted(){
            fetch('https://9de9745c.ngrok.io/getData')
        },
        computed: {
          filteredTasks() {
            if (!this.selectedTasker) {
              return this.tasks
            }

            return this.tasks.filter(task => task['assignee_id'] === this.selectedTasker)
          },
          lines() {
            let lines = [];

            if (!this.selectedTasker) {
              return lines
            }

            let lastPoint = null;

            this.filteredTasks.map(task => {
                if (lastPoint) {
                  lines.push([[lastPoint.lat, lastPoint.lng], [task.lat, task.lng]])
                }

              lastPoint = {lat: task.lat, lng: task.lng}
            })

            return lines
          }
        },
        methods: {
          onClickTasker: function(taskerId) {
            this.selectedTasker = taskerId;

            var bounds = new L.LatLngBounds([this.filteredTasks.map(task => [task.lat, task.lng])])
            this.$refs.map.fitBounds(bounds)
          },
          resetSelection: function() {
            this.selectedTasker = null;

            var bounds = new L.LatLngBounds([this.filteredTasks.map(task => [task.lat, task.lng])])
            this.$refs.map.fitBounds(bounds)
          }
        },
        components: {
          LMap,
          LTileLayer,
          LMarker,
          LPolyline,
          LTooltip
        }
    }
</script>

<style>
    @import "~leaflet.markercluster/dist/MarkerCluster.css";
    @import "~leaflet.markercluster/dist/MarkerCluster.Default.css";

    html, body {
        height: 100%;
        width: 100%;
        display: flex;
    }

    #app {
        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: #2c3e50;
        display: flex;
        flex: 1;
        flex-direction: column;
    }

    .reset {
        padding: 11px 15px;
        border: 1px solid #d3d3d3;
        border-radius: 70px;
        cursor: pointer;
        transition: all 200ms;
    }

    .reset:hover {
        background-color: rgb(235, 235, 235);
    }

    .list-item {
        height: 60px;
        display: flex;
        padding-left: 24px;
        align-items: center;
        border-bottom: 1px solid rgb(235, 235, 235);
        cursor: pointer;
        transition: all 200ms;
    }

    .list-item.selected {
        background-color: rgb(235, 235, 235);
    }

    .list-item:hover {
        background-color: rgb(235, 235, 235);
    }

    .tasker-list {
        width: 30%;
        overflow: auto;
    }

    .map-container {
        flex: 1;
        display: flex;
    }

    .header-image {
        margin-right: 24px;
    }

    .header-left-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .header {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        border-bottom: 1px solid rgb(235, 235, 235);
    }
</style>