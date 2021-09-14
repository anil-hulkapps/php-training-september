import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

//Master Layout
import Master from './../layouts/Master.vue';
import Onboarding from "./../layouts/Onboarding";
import Base from "./../layouts/Base";

import WelcomePage from "../pages/Steps/WelcomePage";
import Step1 from "../pages/Steps/Step1";
import Step2 from "../pages/Steps/Step2";
import Step3 from "../pages/Steps/Step3";
import Step4 from "../pages/Steps/Step4";
import Step5 from "../pages/Steps/Step5";
import Step6 from "../pages/Steps/Step6";
import SetupComplete from "../pages/Steps/SetupComplete";
//import Dashboard from './../pages/Dashboard/Index.vue';
import Settings from "../pages/Settings";
import Products from "../pages/Products";
import Transactions from "../pages/Transactions";
import DemoTransactions from "../pages/Transaction";

let routes = [
    {
        path: '/',
        component: Base,
        children: [

            {
                path: '',
                component: Master,
                children: [
                    {
                        path: '/settings',
                        component: Settings,
                        name: 'Settings',
                        meta: {
                            title: 'Settings',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/products',
                        component: Products,
                        name: 'Products',
                        meta: {
                            title: 'Products',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/transactions',
                        component: Transactions,
                        name: 'Transactions',
                        meta: {
                            title: 'Transactions',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/demo-transactions',
                        component: DemoTransactions,
                        name: 'Transactions',
                        meta: {
                            title: 'Transactions',
                            requiresAuth: true
                        }
                    }
                ]
            },
            {
                path: '/welcome-page',
                component: Onboarding,
                children: [
                    {
                        path: '/welcome-page',
                        component: WelcomePage,
                        name: 'welcome-page',
                        meta: {
                            title: 'welcome-page',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/step-1',
                        component: Step1,
                        name: 'Step1',
                        meta: {
                            title: 'Step 1',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/step-2',
                        component: Step2,
                        name: 'Step2',
                        meta: {
                            title: 'Step 2',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/step-3',
                        component: Step3,
                        name: 'Step3',
                        meta: {
                            title: 'Step 3',
                            requiresAuth: true
                        }
                    },
                    /*{
                        path: '/step-4',
                        component: Step4,
                        name: 'Step4',
                        meta: {
                            title: 'Step 4',
                            requiresAuth: true
                        }
                    },*/
                    {
                        path: '/step-4',
                        component: Step5,
                        name: 'Step4',
                        meta: {
                            title: 'Step 4',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/step-5',
                        component: Step6,
                        name: 'Step5',
                        meta: {
                            title: 'Step 5',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/step-6',
                        component: Step6,
                        name: 'Step6',
                        meta: {
                            title: 'Step 6',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/setup-comptele',
                        component: SetupComplete,
                        name: 'SetupComplete',
                        meta: {
                            title: 'Setup Complete',
                            requiresAuth: true
                        }
                    },
                    {
                        path: '/settings',
                        component: Settings,
                        name: 'Settings',
                        meta: {
                            title: 'Settings',
                            requiresAuth: true
                        }
                    },
                ]
            }
        ]
    },
];

const router = new VueRouter({
    mode: 'history',
    routes,
    scrollBehavior() {
        return {
            x: 0,
            y: 0,
        };
    },
});


export default router;
