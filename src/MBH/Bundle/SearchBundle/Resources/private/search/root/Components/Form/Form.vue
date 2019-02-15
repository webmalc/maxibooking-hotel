<template>
    <transition name="fade" appear>
    <div class="data-form">
        <div class="input">
            <i class="fa fa-calendar" title="" data-toggle="tooltip" data-original-title="Заезд"></i>&nbsp;
            <RangePicker/>
        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни до даты поиска."></i>&nbsp;
            <input type="number" v-model="additionalBegin" class="input-xxs" min="0" :max="maxAdditionalDays">
        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни после даты поиска."></i>&nbsp;
            <input type="number" v-model="additionalEnd" class="input-xxs" min="0" :max="maxAdditionalDays">
        </div>

        <div class="input"><i class="fa fa-male" title="" data-toggle="tooltip" data-original-title="Взрослые."></i>&nbsp;
            <input type="number" required="required" min="0"
                   :max="maxAdults" class="input-xxs" v-model="adults">
        </div>

        <div class="input children_age_holder_parent">
            <i class="fa fa-child" title="" data-toggle="tooltip" data-original-title="Дети."></i>&nbsp;
            <input type="number" required="required" min="0" :max="maxChildren" class="input-xxs" v-model="children">
            &nbsp;
            <i class="fa fa-sort-numeric-asc" title="" data-toggle="tooltip" data-original-title="Возраст детей."></i>
            <div v-if="children" class="children_age_holder_vue">
                <div>
                    <ChildrenAgeInput v-for="key in children" :key="key-1"/>
                </div>
            </div>
        </div>

        <div class="input"><i class="fa fa-bed" title="" data-toggle="tooltip" data-original-title="Тип номера."></i>&nbsp;
            <RoomTypeInput/>
        </div>

        <div class="input">
            <i class="fa fa-file-text-o" title="" data-toggle="tooltip" data-original-title="Номер заказа"></i>&nbsp;
            <input v-model="orderId" type="number" class="only-int input-xs" min="0">
        </div>

        <div class="input">
            <i class="fa fa-exclamation-circle" title="" data-toggle="tooltip" data-original-title="Игнорировать условия и ограничения"></i>&nbsp;
            <CheckBox v-model="isForceBooking" :label-id="'isForceBookingLabelId'"/>
        </div>

        <div class="input">
            <i class="fa fa-star" title="" data-toggle="tooltip" data-original-title="Строгое соответствие спец. предложений"></i>&nbsp;
            <CheckBox v-model="isSpecialStrict" :label-id="'isSpecialStrictLabelId'"/>
        </div>

        <div class="input">
            <SearchButton />
        </div>

    </div>
    </transition>

</template>

<script lang="ts">
    import RangePicker from './RangePicker.vue'
    import ChildrenAgeInput from './ChildrenAgeInput.vue'
    import RoomTypeInput from './RoomTypeInput.vue';
    import CheckBox from './CheckBox.vue';
    import SearchButton from './SearchButton.vue';

    export default {
        name: "AppForm",

        components: {
            RangePicker,
            ChildrenAgeInput,
            RoomTypeInput,
            CheckBox,
            SearchButton
        },
        data() {
            return {
                maxAdults: 6,
                maxChildren: 6,
                maxAdditionalDays: 7,
            }
        },
        computed: {
            additionalBegin: {
                get() {
                    return this.$store.state.form.additionalBegin
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.maxAdditionalDays));
                    this.$store.commit('form/setAdditionalBegin', Number(value))
                }
            },
            additionalEnd: {
                get() {
                    return this.$store.state.form.additionalEnd
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.maxAdditionalDays));
                    this.$store.commit('form/setAdditionalEnd', Number(value))
                }
            },
            adults: {
                get() {
                    return this.$store.state.form.adults
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.maxAdults));
                    this.$store.commit('form/setAdults', value)
                }
            },
            children: {
                get() {
                    return this.$store.state.form.children
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.maxChildren));
                    this.$store.commit('form/setChildren', value);
                }
            },
            orderId: {
                get() {
                    return this.$store.state.form.orderId || '';
                },
                set(value) {
                    this.$store.commit('form/setOrderId', Number(value));
                }
            },
            isForceBooking: {
                get() {
                    return this.$store.state.form.isForceBooking;
                },
                set(value) {
                    this.$store.commit('form/setIsForceBooking', Boolean(value))
                }
            },
            isSpecialStrict: {
                get() {
                    return this.$store.state.form.isSpecialStrict;
                },
                set(value) {
                    this.$store.commit('form/setIsSpecialStrict', Boolean(value))
                }
            },

        }
    }
</script>

<style scoped lang="scss">
    .data-form > .input {
        display: inline-block;
    }

    .input-xxs {
        width: 40px !important;

    }
    .children_age_holder_parent {
        position: relative;
    }

    .children_age_holder_vue {
        position: absolute;
        width: 380px;
        height: 25px;
        top: -30px;
        left: 10px;

        & > div {
            display: inline-flex;
        }

        .children_age_select {
            width: 40px !important;
        }
    }

    .fade-enter-active{
        transition: opacity .5s;
    }
    .fade-leave-active {
        transition: opacity .2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }

</style>
