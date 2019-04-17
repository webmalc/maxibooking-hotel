<template>
    <div>
        <section class="s_results">
            <div class="oneblockinrdm room-type-row ">
                <div class="leftrdm">
                    <div class="imghotel"
                         style="background:url('https://az.maxibooking.ru/media/cache/thumb_275x210/upload/roomTypes/1488550036899.jpeg')no-repeat;background-size:cover;">
                        <div class="inghotelline">

                            <a style="display: none;"
                               class="fancybox"
                               href="https://az.maxibooking.ru/upload/roomTypes/1488550077786.jpeg"
                               rel="cat1">
                                <img src="https://az.maxibooking.ru/media/cache/thumb_275x210/upload/roomTypes/1488550077786.jpeg"
                                     alt="">
                            </a>

                            <a style="" class="fancybox"
                               href="https://az.maxibooking.ru/upload/roomTypes/1488550081817.jpeg"
                               rel="cat1"> <img
                                    src="https://az.maxibooking.ru/media/cache/thumb_275x210/upload/roomTypes/1488550081817.jpeg"
                                    alt="">
                            </a>

                            <a style="" class="fancybox"
                               href="https://az.maxibooking.ru/upload/roomTypes/1488550084387.jpeg"
                               rel="cat1"> <img
                                    src="https://az.maxibooking.ru/media/cache/thumb_275x210/upload/roomTypes/1488550084387.jpeg"
                                    alt="">
                            </a>

                            <a style="" class="fancybox"
                               href="https://az.maxibooking.ru/upload/roomTypes/1488550088871.jpeg"
                               rel="cat1"> <img
                                    src="https://az.maxibooking.ru/media/cache/thumb_275x210/upload/roomTypes/1488550088871.jpeg"
                                    alt="">
                            </a>

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
                    <a href="javascript:void(0)" class="btn-booking-reservation">
                        <p class="btmone btmonenotspec">Отправить заявку</p>
                    </a>
                    <a href="javascript:void(0)" class="btn-booking"><p class="btmtwo">Купить онлайн</p></a>
                </div>
                <div class="clear"></div>
            </div>
        </section>


    </div>
</template>

<script>
    import moment from 'moment';

    moment.locale('ru');
    export default {
        name: "ResultInstance",
        props: ['roomType', 'data'],
        data() {
            return {
                picked: 0,
                url: 'https://az.maxibooking.ru'
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
                return `${this.url}/${this.roomType.mainImage.webPath}`;
            },
            images() {
                let images;
                let paths = [];
                if (Array.isArray(this.roomType.images)) {
                    images = this.roomType.images.filter(image => !image.isMain)
                }
                if (images.length) {
                    images.forEach(image => paths.push(`${this.url}/${image.webPath}`))
                }

                return paths;
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
            }
        }

    }
</script>


<style>
    @import '~pretty-checkbox/dist/pretty-checkbox.min.css';
</style>
<style scoped>
    .s_results p {
        margin: 5px 0;
        text-align: left;
    }
</style>