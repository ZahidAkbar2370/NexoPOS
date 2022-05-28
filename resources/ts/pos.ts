/**
 * will bootstrap time
 */
import Vue from 'vue';
import './shared/time';
import './components/components';
import * as baseComponents from './components/components';

import VirtualCollection from 'vue-virtual-collection';
import { NsHotPress } from './libraries/ns-hotpress';

const NsPos                     =   () => import( './pages/dashboard/pos/ns-pos.vue' );
const NsPosCart                 =   () => import( './pages/dashboard/pos/ns-pos-cart.vue' );
const NsPosGrid                 =   () => import( './pages/dashboard/pos/ns-pos-grid.vue' );

( window as any ).nsComponents  =   { ...baseComponents };
( window as any ).nsHotPress    =   new NsHotPress;

Vue.use( VirtualCollection );

new Vue({
    el: '#pos-app',
    mounted() {
        // ...
    },
    components: {
        NsPos,
        NsPosCart,
        NsPosGrid,
        ...( window as any ).nsComponents,
    }
})