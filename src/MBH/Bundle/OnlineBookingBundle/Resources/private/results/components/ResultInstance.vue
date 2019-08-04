<template>
    <div>
        <section class="s_results">
            <div class="oneblockinrdm room-type-row ">
                <div class="leftrdm">
                    <div class="imghotel"
                         @click.self="showLightbox(mainImageIndex)"
                         :style="{
                            'background-image': `url(${mainImage.thumb})`,
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover'

                         }">
                        <span v-if="additionalDates" class="special-strip">
                            Другие даты
                        </span>
                        <div class="inghotelline">
                            <template v-for="(image, key) in images">
                                <img v-if="!image.isMain"  style="margin: 4px;"  :src="image.thumb" @click.prevent="showLightbox(key)" :key="key">
                            </template>
                        </div>
                    </div>
                </div>
                <div class="rightrdm">
                    <div class="leftinrdm">
                        <p class="titlerdm">
                            <a :href="hotelLink"  class="hotel_link" target="_blank">
                                {{hotelInfo.title}}
                            </a> &nbsp;
                        </p>
                        <p class="numtype">
                            <b>Тип номера:</b>
                            <br>
                            <a class="room_link"
                               href="https://azovsky.ru/azovland/tipi-nomerov/nomera-komfort-v-domikah/"
                               target="_blank">
                                {{categoryInfo.title}}</a>
                        </p>
                        <p class="gueststype"><b>Гости:</b> {{formData.adults}} взр. {{formData.children ? `${formData.children} реб`: '' }}</p>
                        <a :href="`https://azovsky.ru${eat.url}`"
                           class="eat_link"
                           target="_blank">
                            <p class="eattype">
                                <img src="/images/vl.png" alt="">
                                {{eat.number}}-разовое питание<br>шведский стол
                            </p>
                        </a>
                        <div class="iconstype"></div>
                    </div>
                    <div class="rightinrdm">
                        <p class="titlerdm">Цена за {{nights}} ночей</p>
                        <div class="clear"></div>
                        <p class="pricerdm">
                            <span class="noneprice" v-if="currentOldPrice"> {{currentOldPrice}}&#8381;</span>
                            <span class="newprice">{{currentPrice}}&#8381;</span>
                        </p>
                        <p class="datesrdm">{{showDate(begin)}} - {{showDate(end)}}</p>
                        <p class="daysrdm">{{days}} дней / {{nights}} ночей</p>
                        <p class="bronsrdm"></p></div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                <div class="lefttblrdm">
                    <table class="pricetbl">
                        <thead>
                        <tr class="flinetbl">
                            <td>Тариф</td>
                            <td>Скидка</td>
                            <td>Цена</td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody class="tariff-container">
                        <tr
                                :class="tariffClass(key)"
                                v-for="(result, key) in data"
                                :key="`${roomType.id}-${key}`"
                                @click="trClick(key)"
                        >
                            <td>{{tariffInfo(result.tariff).fullTitle}}</td>
                            <td class="redtd"></td>
                            <td>{{result.prices[0].total}}&#8381;</td>
                            <td>
                                <p-input
                                        type="radio"
                                        class="p-icon p-jelly p-round p-bigger p-svg"
                                        :name="`${roomType.id}${showDate(begin)}${showDate(end)}`"
                                        :value="key" v-model="picked"
                                        color="success"
                                >
                                    <svg slot="extra" class="svg svg-icon" viewBox="0 0 20 20">
                                        <path d="M7.629,14.566c0.125,0.125,0.291,0.188,0.456,0.188c0.164,0,0.329-0.062,0.456-0.188l8.219-8.221c0.252-0.252,0.252-0.659,0-0.911c-0.252-0.252-0.659-0.252-0.911,0l-7.764,7.763L4.152,9.267c-0.252-0.251-0.66-0.251-0.911,0c-0.252,0.252-0.252,0.66,0,0.911L7.629,14.566z"
                                              style="stroke: white;fill:white"></path>
                                    </svg>
                                </p-input>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="righttblrdm">
                    <div class="spros">Пользуется спросом!</div>

                    <div class="btn-booking-reservation" @click="saveOrderData('reserve')">
                        <p class="btmone btmonenotspec">Отправить заявку</p>
                    </div>

                    <div class="btn-booking" @click="saveOrderData('online')">
                        <p class="btmtwo">Купить онлайн</p>
                    </div>

                </div>
                <div class="clear"></div>
            </div>
        </section>
        <LightBox :images="images" :showLightBox="false" ref="lightbox"></LightBox>
    </div>
</template>

<script lang="ts">
    import * as moment from 'moment';
    import LightBox from 'vue-image-lightbox';

    const createDate = date => moment(date, 'DD.MM.YYYY');

    moment.locale('ru');
    export default {
        name: "ResultInstance",
        props: ['roomType', 'data'],
        components: {
            LightBox,
        },
        data() {
            return {
                picked: 0,
            }
        },
        computed: {
            formData() {
                return this.$store.state.form;
            },
            nights() {
                return this.end.diff(this.begin, 'days');
            },
            days() {
                return this.nights + 1;
            },
            begin() {
                return createDate(this.data[0].begin);
            },
            end() {
                return createDate(this.data[0].end);
            },
            searchBegin() {
                return createDate(this.$store.state.results.currentConditions.begin);
            },
            searchEnd() {
                return createDate(this.$store.state.results.currentConditions.end);
            },
            additionalDates() {
                return this.begin.diff(this.searchBegin) || this.end.diff(this.searchEnd);
            },
            selectedResult() {
                return this.data[this.picked];
            },
            currentPrice() {
                return this.selectedResult.prices[0].total;
            },
            currentOldPrice() {
                const discountArray = this.selectedResult.prices[0].priceByDay[0].discount;
                let price = null;
                if (discountArray.length > 0) {
                    const discountObject = discountArray[0];
                    const discount = discountObject[Object.keys(discountObject)[0]];
                    price = this.currentPrice * 100 / (100 - discount);
                    price = Math.round(price);
                }

                return price;
            },
            mainImage() {
                let images;
                if (Array.isArray(this.roomType.frontImages)) {
                    images = this.roomType.frontImages.filter(image => image.isMain)
                }

                let mainImage =  images.shift();
                if (!mainImage) {
                    mainImage = {
                        src: '',
                        thumb: ''
                    }
                }
                return mainImage;

            },
            images() {
                return this.roomType.frontImages;
            },
            mainImageIndex() {
                return this.images.findIndex(image => image.isMain);
            },
            hotelLink() {
                return this.hotelInfo.links.url;
            },
            eat() {
                return {
                    number: this.hotelInfo.links.eat.number,
                    url: this.hotelInfo.links.eat.url
                }
            },
            hotelInfo() {
                const hotelId = this.roomType.hotel.$id.$id;

                return this.$store.getters['results/getHotelInfo'](hotelId);
            },
            categoryInfo() {
                const categoryId = this.roomType.category.$id.$id;

                return this.$store.getters['results/getCategoryInfo'](categoryId);
            },
        },
        methods: {
            showDate(dateMoment) {
                return dateMoment.format('DD.MM.YYYY')
            },
            trClick(key) {
                this.picked = key;
            },
            tariffClass(index) {
                return {
                    'tariff-row': true,
                    alerting: this.picked === index
                }
            },
            showLightbox(index = 0) {
                this.$refs.lightbox.showImage(index);
            },
            saveOrderData(type) {
                this.$store.commit('order/selectOrder', this.selectedResult);
                this.$router.push({name: 'order', params: {type}});
            },
            tariffInfo(tariffId) {
                return this.$store.getters['results/getTariffInfo'](tariffId);
            }
        }

    }
</script>


<style>
    @import '~pretty-checkbox/dist/pretty-checkbox.min.css';
    @import '~vue-image-lightbox/dist/vue-image-lightbox.min.css';
</style>
<style scoped>
    .s_results p {
        margin: 5px 0;
        text-align: left;
    }
</style>