<template>
    <div>
        <div v-if="rbkReady">Процесс оплаты
            <div v-if="paymentStatus === 'closed'" @click="openRBKPayment">Кнопка отрыть оплату заново.</div>
        </div>
        <div v-else>Тут крутилка пока не готовы данные для оплаты</div>
    </div>
</template>

<script>
    export default {
        name: "OnlinePayment",
        data() {
            return {
                checkout: {},
                rbkReady: false,
                paymentStatus: ''
            }
        },
        beforeCreate() {
            let rbk = document.createElement('script');
            rbk.setAttribute('src', "https://checkout.rbk.money/checkout.js");
            rbk.setAttribute('class', 'rbkmoney-checkout');
            rbk.onload = () => {
                this.rbkReady = true;
            };
            document.head.appendChild(rbk);

        },
        computed: {
            orderResult() {
                return this.$store.state.order.orderResult;
            },
            rbkOpenReady() {
                return this.rbkReady && Object.keys(this.orderResult).length && this.orderResult.invoice.status === true;
            },
            rbkInvoiceError() {
                return this.orderResult.invoice.status !== true;
            }
        },
        methods: {
            openRBKPayment() {
                if (!Object.keys(this.checkout).length) {
                    console.log('Error!!!!!')
                }
                this.checkout.open();
            }
        },
        watch: {
            rbkOpenReady(ready) {
                if (ready) {
                    const data = this.orderResult.invoice.data;
                    this.checkout = window.RbkmoneyCheckout.configure(
                        {
                            "invoiceID": data.invoiceID,
                            "invoiceAccessToken": data.invoiceAccessToken,
                            "name": data.name,
                            "obscureCardCvv": data.obscureCardCvv,
                            "requireCardHolder": data.requireCardHolder,
                            "description": data.description,
                            "email": data.email,
                            opened:  ()  => this.paymentStatus = 'opened',
                            closed: () => this.paymentStatus = 'closed',
                            finished: () => this.paymentStatus = 'finished'
                        }
                    );
                    this.openRBKPayment();
                }
            }
        }


    }
</script>

<style scoped>

</style>