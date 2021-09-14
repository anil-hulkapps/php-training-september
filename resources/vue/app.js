window._ = require('lodash');

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import moment from 'moment';

window.moment = moment;
window.Vue = require('vue').default;

Vue.filter('formatDate', function(value) {
    if (value) {
        return moment(String(value)).format('ddd DD, MMM')
    }
});

import PolarisVue from '@hulkapps/polaris-vue';
import '@hulkapps/polaris-vue/dist/polaris-vue.css';
Vue.use(PolarisVue);


/*import Copy from 'v-copy'
Vue.use(Copy);*/

/*import VueI18n from 'vue-i18n'
Vue.use(VueI18n)*/

// Components
import './components';

import router from './routes';

Vue.directive('click-outside', {
    bind: function (el, binding, vnode) {
        el.clickOutsideEvent = function (event) {
            // here I check that click was outside the el and his childrens
            if (!(el == event.target || el.contains(event.target))) {
                // and if it did, call method provided in attribute value
                vnode.context[binding.expression](event);
            }
        };
        document.body.addEventListener('click', el.clickOutsideEvent)
    },
    unbind: function (el) {
        document.body.removeEventListener('click', el.clickOutsideEvent)
    },
});

/*
if ((window.self !== window.top)) {
    let cookieValue = null;
    let affiliate = null;
    let appCode = null;
    if (readCookie('hulkapps_ref')) {
        cookieValue = readCookie('hulkapps_ref');
        let cookieValues = cookieValue.split('|');
        appCode = cookieValues[0];
        affiliate = (appCode == process.env.MIX_TAPFILIATE_APP_CODE) ? cookieValues[1] : affiliate;
    }
    let affiliate_data = {'affiliate': affiliate};
    window.axios.post('/tapfiliate', affiliate_data).then((response) => {
        if (response.data.status === 'success') {
            if (cookieValue !== null && appCode == process.env.MIX_TAPFILIATE_APP_CODE) deleteCookie('hulkapps_ref');
        }
    });
}*/

/*import messages from './lang/messages'*/

/*const i18n = new VueI18n({
    locale: 'en-US',
    messages
});*/

const app = new Vue({
    el: '#app',
    router
});
