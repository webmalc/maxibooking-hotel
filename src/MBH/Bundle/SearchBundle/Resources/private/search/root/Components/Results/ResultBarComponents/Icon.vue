<template>
    <span>
        <i v-if="!isCached" :class="icon"></i>
        <a v-if="isCached" href="#" @click.prevent="invalidate" :style="cursor" ><i :class="icon" data-toggle="tooltip" :title="invalidationTitle"></i></a>
    </span>
</template>

<script lang="ts">
    export default {
        name: "Icon",
        props: {
            isCached: Boolean,
            cacheItemId: String
        },
        computed: {
            icon: function () {
                return {
                    'fa': true,
                    'fa-paper-plane-o': !this.isCached,
                    'fa-envelope': this.isCached && !this.invalidated,
                    'fa-circle-o-notch': this.isCached && this.invalidated
                }
            },
            invalidationTitle: function () {
                return (this.isCached && !this.invalidated) ? 'Инвалидация' : '';
            },
            cursor: function  () {
                return  {
                    cursor: this.invalidated ? 'default' : 'pointer'
                }
            }
        },
        methods: {
            invalidate(a,b,c) {
                console.log('must invalidate');
            }
        }
    }
</script>

<style scoped>

</style>