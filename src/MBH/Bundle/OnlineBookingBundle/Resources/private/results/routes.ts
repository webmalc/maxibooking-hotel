import ResultsWrapper from './components/ResultsWrapper.vue';
import Order from './components/Order.vue';
import OnlinePayment from './components/OnlinePayment.vue'

const routes = [
    {
        path: '/',
        name: 'search',
        component: ResultsWrapper
    },
    {
        path: '/order',
        name: 'order',
        component: Order
    },
    {
        path: '/online-payment',
        name: 'onlinePayment',
        component: OnlinePayment
    }
];

export default routes;