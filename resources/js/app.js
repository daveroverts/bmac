/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import $ from 'jquery';
import 'jquery-ui/ui/widgets/datepicker';
// Importing Font Awesome
import {dom, library} from '@fortawesome/fontawesome-svg-core'
import {far} from '@fortawesome/free-regular-svg-icons'
import {fas} from '@fortawesome/free-solid-svg-icons'
import {fab} from '@fortawesome/free-brands-svg-icons'

window.$ = window.jQuery = $;

$('.datepicker').datepicker({
    dateFormat: "dd-mm-yy",
    minDate: 0,
    showButtonPanel: true,
    showOtherMonths: true,
    selectOtherMonths: true
});

library.add(far, fas, fab);

dom.watch();

// TinyMCE
import tinymce from 'tinymce';
import 'tinymce/themes/silver/theme';

import 'tinymce/plugins/code'
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';

tinymce.init({
    selector: '#description',
    plugins: ['code', 'paste', 'link']
});

import Bugsnag from '@bugsnag/js'
Bugsnag.start(process.env.MIX_BUGSNAG_API_KEY)

// window.Vue = require('vue');
// import VueSweetalert2 from 'vue-sweetalert2';
// Vue.use(VueSweetalert2);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));
//
// const app = new Vue({
//     el: '#app'
// });
