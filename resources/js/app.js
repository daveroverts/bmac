/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import './airport-select';

// Importing Font Awesome
import { dom, library } from '@fortawesome/fontawesome-svg-core'
import {
    faCalendar,
    faCheck,
    faChevronRight,
    faClock,
    faCopyright,
    faEdit,
    faEnvelope,
    faFileExcel,
    faFileExport,
    faFileImport,
    faPlus,
    faSave,
    faSearch,
    faTimes,
    faTrash,
} from '@fortawesome/free-solid-svg-icons'

library.add(
    faCalendar,
    faCheck,
    faChevronRight,
    faClock,
    faCopyright,
    faEdit,
    faEnvelope,
    faFileExcel,
    faFileExport,
    faFileImport,
    faPlus,
    faSave,
    faSearch,
    faTimes,
    faTrash,
);

dom.watch();
