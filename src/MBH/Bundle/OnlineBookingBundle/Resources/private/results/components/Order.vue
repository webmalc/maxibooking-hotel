<template>
    <div v-if="isError">
        Произошла непредвиденная ошибка в системе. Пожалуйста, позвоните нам.
    </div>
    <div v-else>
        <div id="sign-page" class="col-md-8">

            <h3>Персональная информация</h3>

            <div v-if="isOnline" class="error_pay" style="display:block;">Внимание! Оплачивая туристические услуги, Вы соглашаетесь с
                условиями публичного <a href="https://yadi.sk/i/bNDRgure3TZhSq" target="_blank">договора-оферты.</a></div>

            <div id="form" class="ss">
                <div class="form-group">
                    <label class="control-label required" for="form_firstName">Имя</label>
                    <input type="text" id="form_firstName" name="form[firstName]" required="required" class="form-control" v-model="order.firstName">
                </div>

                <div class="form-group">
                    <label class="control-label required" for="form_lastName">Фамилия</label>
                    <input type="text" id="form_lastName" name="form[lastName]" required="required" class="form-control" v-model="order.lastName">
                </div>

                <div class="form-group">
                    <label class="control-label" for="form_patronymic">Отчество</label>
                    <input type="text" id="form_patronymic" name="form[patronymic]" class="form-control" v-model="order.patronymic">
                </div>

                <div class="form-group">
                    <label class="control-label required" for="form_phone">Телефон</label>
                    <input type="text" id="form_phone" name="form[phone]" required="required" class="form-control" v-model="order.phone">
                </div>

                <div class="form-group">
                    <label class="control-label" for="form_email">Email</label>
                    <input type="email" id="form_email"
                           name="form[email]"
                           class="form-control"
                           v-model="order.email"
                    >
                </div>


                <div class="personal">
                <p-check class="p-default p-fill order" color="success" v-model="order.accept">
                    Я согласен на обработку моих персональных данных.
                </p-check>

                <p-check v-if="isOnline" class="p-default p-fill order" color="success" v-model="order.offerta">
                    Принимаю условия <a href="https://yadi.sk/i/bNDRgure3TZhSq" target="_blank">договора-оферты.</a>
                </p-check>
                </div>


                <div v-if="isOnline" class="form-group">
                    <label class="control-label" for="form_cash">Сумма к оплате согласно тарифа</label>
                    <input type="text" id="form_cash" name="form[cash]" disabled="disabled" class="form-control" v-model="formPrice">
                </div>

            </div>

            <hr>
            <div><p class="reqinfo">* - поля обязательные к заполнению</p></div>
            <div class="submit">
                <button @click.prevent="createOrder" type="submit" name="submit" :class="classes['button']" :disabled="isButtonDisabled">
                    <span>{{isOnline ? 'Забронировать' : 'Отправить заявку'}}</span>
                </button>
            </div>

        </div>

        <div id="right-column" class="col-md-4">
            <div id="info">
                <div class="title">Детали бронирования</div>
                <div class="item row">
                    <div class="col-xs-4"><a href="#" @click.prevent="showLightBox">
                        <img :src="mainImage.thumb"
                            width="70px" height="70px"> </a></div>
                    <div class="col-xs-8">
                        <div>{{orderData.resultRoomType.hotelName}}</div>
                        <div class="numtype">Тип номера</div>
                        <div><a class="room_link"
                                href="https://azovsky.ru/azovsky/azovskiy-rooms/azovskiy-rooms-standart/"
                                target="_blank">{{orderData.resultRoomType.categoryName}}</a></div>
                    </div>
                </div>
                <div class="date-info row">
                    <div class="arrival col-xs-4">
                        <div>Заезд</div>
                        <div class="date"> {{showDate(begin)}}
                        </div>
                        <div class="date"> с 13:00
                        </div>
                    </div>
                    <div class="days col-xs-4">
                        <div><i class="fa fa-clock-o fa-2x"></i></div>
                        <div>Ночей: {{nights}}</div>
                    </div>
                    <div class="departure col-xs-4">
                        <div>Выезд</div>
                        <div class="date"> {{showDate(end)}}
                        </div>
                        <div class="date"> до 11:00
                        </div>
                    </div>
                </div>
                <div class="guests">
                    <div>Гости: {{adults}}{{children ? `+${children}`: ''}}</div>
                    <div>Тариф: {{orderData.resultTariff.fullName}}</div>
                </div>
                <div class="row" style="margin-top: 15px">
                    <div class="title" style="display: inline-block;">Стоимость</div>
                    <div class="pull-right"
                         style="color: #41bc19; display: inline-block; font-weight: bold; font-size: 18px">{{totalPrice}}
                        <i class="fa fa-ruble"></i></div>
                </div>
            </div>
            <div id="questions">
                <div class="title">Остались вопросы?</div>
                <div class="description"><p>Свяжитесь с нами!</p>
                    <p>Звонок вас ни к чему не обязывает,
                        но обеспечит дополнительной
                        информацией</p></div>
                <div class="phone"><i class="fa fa-phone fa-2x"></i> <span>8-800-775-15-41</span></div>
                <div class="email"><a href="/mail-us">mail@azovsky.ru</a></div>
            </div>
        </div>
        <LightBox :images="image" :showLightBox="false" ref="orderLightBox"></LightBox>


    </div>
</template>

<script>
    import * as moment from 'moment';
    import LightBox from 'vue-image-lightbox';
    export default {
        name: "Order",
        components: {
            LightBox
        },
        props: ['type'],
        beforeMount() {
            if (!Object.keys(this.orderData).length) {
                this.$router.replace({name: "search"});
            }
        },
        data() {
            return {
                order: {
                    firstName: '',
                    lastName: '',
                    patronymic: '',
                    phone: '',
                    email: '',
                    accept: false,
                    offerta: false
                }
            }
        },
        computed: {
            isOnline() {
                return this.type === 'online'
            },
            orderData() {
                return this.$store.state.order.currentOrder;
            },
            isError() {
                return !Object.keys(this.$store.state.order.currentOrder).length || this.$store.state.order.status === 'error';
            },
            adults() {
                return this.orderData.resultConditions.adults
            },
            children() {
                return this.orderData.resultConditions.children
            },
            begin() {
                return moment(this.orderData.begin);
            },
            end() {
                return moment(this.orderData.end);
            },
            nights() {
                return this.end.diff(this.begin, 'days');
            },
            days() {
                return this.nights + 1;
            },
            isButtonDisabled() {
                return !this.order.accept || (!this.order.offerta && this.type === 'online') || this.$store.state.order.status !== 'new' || !this.isFormValidated
            },
            isFormValidated() {
                const requiredFields = Boolean(this.order.firstName)
                    && Boolean(this.order.lastName)
                    && Boolean(this.order.phone);

                if (Boolean(this.order.email)) {
                    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                        return requiredFields && re.test(this.order.email);
                }

                return requiredFields;
            },
            classes() {
                return {
                    button: {
                        'bkbutton': true,
                        'disabled': this.isButtonDisabled

                    }
                }
            },
            totalPrice() {
                return this.orderData.prices[0].total;
            },
            formPrice() {
                return `${this.totalPrice} руб.(100% от ${this.totalPrice} руб.)`
            },
            roomTypeId() {
                return this.orderData.resultRoomType.id;
            },
            tariffId() {
                return this.orderData.resultTariff.id;
            },
            image() {
                return this.orderData.resultRoomType.images;
            },
            mainImage() {
                let images;
                if (Array.isArray(this.orderData.resultRoomType.images)) {
                    images = this.orderData.resultRoomType.images.filter(image => image.isMain)
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
            orderResult() {
                return this.$store.state.order.orderResult;
            }
        },
        methods: {
            showDate(dateMoment) {
                return dateMoment.format('DD.MM.YYYY')
            },
            showLightBox() {
                this.$refs.orderLightBox.showImage(0);
            },
            createOrder() {
                this.$store.dispatch('order/createOrder', {personalData: this.order, type: this.type});
            }

        },
        watch: {
            orderResult({type}) {
                let route;
                if (type === 'online') {
                    route = 'onlinePayment';
                } else {
                    route = 'reserveResult';
                }
                this.$router.push({name: route});
            }
        }

    }
</script>

<style lang="less">
    @import "../../../public/css/sign.less";

</style>

<style scoped>
    .order {
        color: #9b9b9b;
        font-size: 14px;
        text-transform: none;
        margin: 5px;
    }
    .personal {
        margin-bottom: 20px;
    }
</style>