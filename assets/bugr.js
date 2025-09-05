/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (bugr.scss in this case)
// import './styles/bugr.scss';
import './styles/bugr.css';

// start the Stimulus application
import '@popperjs/core/dist/esm/index'

import './bootstrap';
import 'bootstrap/js/dist/base-component'
import 'bootstrap/js/dist/dropdown'
import 'bootstrap/js/dist/button'
import 'bootstrap/js/dist/collapse'
import 'bootstrap/js/dist/popover'
import Tooltip from 'bootstrap/js/dist/tooltip'
import 'bootstrap/js/dist/toast'
import TomSelect from "tom-select";
//import 'bootstrap/js/dist'

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))

const selectInputs = document.querySelectorAll('select')
const selectInputsList = [...selectInputs].map(selectInputsEl => new TomSelect(selectInputsEl,{
    create: true,
    sortField: {
        field: "text",
        direction: "asc"
    }
}))
