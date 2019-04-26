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
                            <a href="#" @click.prevent class="hotel_link" target="_blank">
                                {{roomType.hotelName}}
                            </a> &nbsp;
                        </p>
                        <p class="numtype">
                            <b>Тип номера:</b>
                            <br>
                            <a class="room_link"
                               href="https://azovsky.ru/azovland/tipi-nomerov/nomera-komfort-v-domikah/"
                               target="_blank">
                                {{roomType.categoryName}}</a>
                        </p>
                        <p class="gueststype"><b>Гости:</b> {{formData.adults}} взр. {{formData.children}} реб</p>
                        <a href="https://azovsky.ru/shvedskiy-stol/azovland/"
                           class="eat_link"
                           target="_blank">
                            <p class="eattype">
                                <img src="/images/vl.png" alt="">
                                5-разовое питание<br>шведский стол
                            </p>
                        </a>
                        <div class="iconstype"></div>
                    </div>
                    <div class="rightinrdm">
                        <p class="titlerdm">Цена за {{nights}} ночей</p>
                        <div class="clear"></div>
                        <p class="pricerdm">
                            <span class="noneprice"> {{currentOldPrice}}</span> &#8381;
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
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw tariff-spinner"></i>
                        <span class="sr-only">Loading...</span>
                        <tr
                                :class="tariffClass(key)"
                                v-for="(result, key) in data"
                                :key="`${roomType.id}-${key}`"
                                @click="trClick(key)"
                        >
                            <td>{{result.resultTariff.fullName}}</td>
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
                    <router-link :to="{name: 'order', params: {type: 'reserve'}}" class="btn-booking-reservation" @click.native="saveOrderData">
                        <p class="btmone btmonenotspec">Отправить заявку</p>
                    </router-link>

                    <router-link :to="{name: 'order', params: {type: 'online'}}" class="btn-booking" @click.native="saveOrderData">
                        <p class="btmtwo">Купить онлайн</p>
                    </router-link>
                </div>
                <div class="clear"></div>
            </div>
        </section>
        <LightBox :images="images" :showLightBox="false" ref="lightbox"></LightBox>
    </div>
</template>

<script lang="ts">
    import moment from 'moment';
    import LightBox from 'vue-image-lightbox';

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
                return moment(this.data[0].begin);
            },
            end() {
                return moment(this.data[0].end);
            },
            searchBegin() {
                return moment(this.data[0].resultConditions.begin);
            },
            searchEnd() {
                return moment(this.data[0].resultConditions.end);
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
                const price = this.currentPrice * 100 / (100 - this.selectedResult.prices[0].discount);

                return Math.round(price);
            },
            mainImage() {
                let images;
                if (Array.isArray(this.roomType.images)) {
                    images = this.roomType.images.filter(image => image.isMain)
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
                return this.roomType.images;
            },
            mainImageindex() {
                return this.images.findIndex(image => image.isMain);
            }
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
            saveOrderData() {
                this.$store.commit('order/selectOrder', this.selectedResult);
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