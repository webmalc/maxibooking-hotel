<template>
    <span>
        <i v-if="!isCached" :class="icon"></i>
        <a v-if="isCached" href="#" @click.prevent="invalidate" :style="cursor" ><i :class="icon" data-toggle="tooltip" :title="invalidationTitle"></i></a>
    </span>
</template>

<script lang="ts">
    export default {
        name: "Icon",
        props: ['isCached', 'cacheItemId'],
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
        // methods: {
        //     invalidate: function (e) {
        //         if (!this.invalidated) {
        //             this.invalidated = true;
        //             let url = Routing.generate('invalidate_item', {'id': this.cacheItemId});
        //             $.get(url);
        //         }
        //     }
        //
        // },
        data: function () {
            return {
                invalidated: false

            }
        }
    }
</script>

<style scoped>

</style>