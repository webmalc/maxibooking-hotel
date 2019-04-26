import ResultsWrapper from './components/ResultsWrapper.vue';
import Order from './components/Order.vue';
import OnlinePayment from './components/OnlinePayment.vue';
import ReserveResult from './components/ReserveResult.vue';

const routes = [
    {
        path: '/',
        name: 'search',
        component: ResultsWrapper
    },
    {
        path: '/order/:type',
        name: 'order',
        component: Order,
        props: true
    },
    {
        path: '/online-payment',
        name: 'onlinePayment',
        component: OnlinePayment
    },
    {
        path: '/reserveResult',
        name: 'reserveResult',
        component: ReserveResult
    }
];

export default routes;