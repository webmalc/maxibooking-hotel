<template>
    <transition
            name="animated-css"
            enter-active-class="animated fadeIn"
            leave-active-class="animated fadeOut"
            mode="out-in"
            class="animated-status"
            :duration="{enter:200, leave: 200}"
            appear
    >
    <div class="data-form">
        <div class="input">
            <i class="fa fa-calendar" title="" data-toggle="tooltip" data-original-title="Заезд"></i>&nbsp;
            <RangePicker/>
        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни до даты поиска."></i>&nbsp;

<!--            <input type="number" v-model="additionalBegin" class="input-xxs" min="0" :max="access.positiveDaysLimit" :disabled="!access.async_search">-->

            <select class="plain-html input-xxs" v-model="additionalBegin" :disabled="!access.async_search">
                <option :value="0">0</option>
                <option v-for="index in access.positiveDaysLimit" :value="index">{{index}}</option>
            </select>

        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни после даты поиска."></i>&nbsp;

<!--            <input type="number" v-model="additionalEnd" class="input-xxs" min="0" :max="access.negativeDaysLimit" :disabled="!access.async_search">-->
            <select class="plain-html input-xxs" v-model="additionalEnd" :disabled="!access.async_search">
                <option :value="0">0</option>
                <option v-for="index in access.negativeDaysLimit" :value="index">{{index}}</option>
            </select>

        </div>

        <div class="input"><i class="fa fa-male" title="" data-toggle="tooltip" data-original-title="Взрослые."></i>&nbsp;
            <input type="number" required="required" min="1"
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

        <template v-if="access.cache">
        <div class="input">
            <i class="fa fa-envelope" title="" data-toggle="tooltip" data-original-title="Использование кэша для поиска"></i>&nbsp;
            <CheckBox  v-model="isUseCache" :label-id="'isUseCache'"/>
        </div>
        </template>

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
            }
        },
        computed: {
            additionalBegin: {
                get() {
                    return this.$store.state.form.additionalBegin
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.access.positiveDaysLimit));
                    this.$store.commit('form/setAdditionalBegin', Number(value))
                }
            },
            additionalEnd: {
                get() {
                    return this.$store.state.form.additionalEnd
                },
                set(value) {
                    value = Math.max(0, Math.min(Number(value), this.access.negativeDaysLimit));
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
            isUseCache: {
                get() {
                    return this.$store.state.form.isUseCache;
                },
                set(value) {
                    this.$store.commit('form/setIsUseCache', Boolean(value))
                }
            },
            access() {
                return {
                    cache: this.$store.state.access.cache,
                    async_search: this.$store.state.access.async_search,
                    positiveDaysLimit: this.$store.state.access.positiveMaxAddDates,
                    negativeDaysLimit: this.$store.state.access.negativeMaxAddDates,
                }
            }

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
</style>
