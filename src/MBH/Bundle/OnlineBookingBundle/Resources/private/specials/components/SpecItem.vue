<template>
    <div class="oneblockspec">
        <div class="spec-img">
            <div class="main-img"
                 :style="{
                            'background-image': `url(${mainImage.thumb})`,
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover'

                         }"
            >
            <p class="special-strip"> - {{special.discount}}%</p>
        </div>

        <div class="hotel-thumb">
            <p class="food">
                <img src="https://azovsky.ru/images/specnew/spoon.png" alt="">
                <a href="https://azovsky.ru/shvedskiy-stol/azovland/" title="Шведский стол">5-раз. питание шведский
                    стол</a>
            </p>
        </div>
    </div>

    <div class="spec-info"><a class="hotel_link" :href="data.hotelLink.url"><p class="hotel">{{hotel.name}}</p></a> <a
            class="room_link" href="https://azovsky.ru/azovland/tipi-nomerov/semeinie-nomera/" target="_blank"><p
            class="room">{{roomType.categoryName}}</p></a>
        <p class="food"><img src="https://azovsky.ru/images/specnew/spoon.png" alt=""> <a
                href="https://azovsky.ru/shvedskiy-stol/azovland/" title="Шведский стол">5-раз. питание шведский
            стол</a></p></div>
    <div class="spec-date"><p class="date">{{viewBegin}}-{{viewEnd}}</p>
        <p class="daynight">{{days}} днeй/ {{nights}} ночей</p>
        <select class="capacity_choice" v-model="selectedCapacity">
            <option v-for="(price, capacity) in prices" :value="capacity">{{capacityView(capacity)}}</option>
        </select>
    </div>
    <div class="spec-price"><p><span class="spec_new_price">{{currentNewPrice(selectedCapacity) | currency }}</span> &#8381;</p>
        <p class="crossout"><span class="spec_old_price">{{currentOldPrice(selectedCapacity) | currency }}</span> &#8381;</p>
        <div class="clear"></div>
        <input type="submit" value="Бронирую" @click.prevent="formAction()">
    </div>
    <div class="clear"></div>
    </div>
</template>

<script>
    import moment from 'moment';

    moment.locale('ru');
    export default {
        name: "SpecItem",
        props: ['data'],
        data() {
            return {
                selectedCapacity: ''
            }
        },
        mounted() {
            this.selectedCapacity = this.defaultCapacity;
        },
        computed: {
            hotel() {
                return this.data.hotel;
            },
            roomType() {
                return this.data.roomType;
            },
            tariff() {
                return this.data.tariff;
            },
            begin() {
                return moment(this.data.dates.begin, 'DD.MM.YY');
            },
            end() {
                return moment(this.data.dates.end, 'DD.MM.YY');
            },
            viewBegin() {
                return this.data.dates.begin;
            },
            viewEnd() {
                return this.data.dates.end;
            },
            prices() {
                return this.data.prices.prices;
            },
            special() {
                return this.data.special;
            },
            days() {
                return this.nights + 1;
            },
            nights() {
                return this.end.diff(this.begin, 'days');
            },
            defaultCapacity() {
                return this.data.prices.defaultPrice;
            },
            discount() {
                return this.special.discount;
            },
            mainImage() {
                return {
                    thumb: this.roomType.image[0].thumb,
                    full: this.roomType.image[0].full
                }
            },

        },
        methods: {
            currentNewPrice(selectedCapacity) {
                return this.prices[selectedCapacity];
            },
            currentOldPrice(selectedCapacity) {
                return Math.round((100 * this.currentNewPrice(selectedCapacity)) / (100 - this.discount));
            },
            capacityView(capacity) {
                const splited = capacity.split('_');
                const adults = splited[0];
                const children = splited[1];
                let result = `${adults}взр.`;
                if (parseInt(children) !== 0) {
                    result = result.concat(`+${children}реб`)
                }

                return result;

            },
            formAction() {
                const hotel = this.hotel.id;
                const roomType = this.roomType.id;
                const begin = this.data.dates.begin;
                const end = this.data.dates.end;
                const adults = parseInt(this.selectedCapacity.split('_')[0]);
                const children = parseInt(this.selectedCapacity.split('_')[1]);
                const special = this.special.id;

                let uri = `https://azovsky.ru/mbresults/?step=1&search_form[hotel]=${hotel}&search_form[roomType]=${roomType}&search_form[begin]=${begin}&search_form[end]=${end}&search_form[adults]=${adults}&search_form[children]=${children}&search_form[special]=${special}`;
                uri = encodeURI(uri);

                window.open(uri, '_blank');

            }
        }
    }
</script>

<style scoped>

</style>