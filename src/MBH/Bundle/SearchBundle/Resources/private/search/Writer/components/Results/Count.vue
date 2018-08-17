<template>
    <td>
        <select v-if="count > 0" v-model="selected" @change="$emit('quantity', selected)" class="form-control quantity-select input-xxs">
            <option v-for="value in (1, count)" :value="value">{{ value }}</option>
        </select>
    </td>
</template>

<script lang="ts">
    import Vue from "vue";

    export default Vue.extend({
        name: "Count",
        props: ['count', 'quantity'],
        mounted: function () {
            this.$emit('quantity', this.selected)
        },
        data: function () {
            return {
                selected: this.quantity
            }
        }
    });
</script>

<style scoped>

</style>