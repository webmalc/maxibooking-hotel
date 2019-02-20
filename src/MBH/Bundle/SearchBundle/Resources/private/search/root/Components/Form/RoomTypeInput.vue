<template>
    <select class="plain-html" multiple="multiple"></select>
</template>

<script>
    export default {
        name: "RoomTypeInput",
        computed: {
            selections() {
                return this.$store.state.form.roomTypeSelections;
            }
        },
        mounted() {
            $(this.$el).select2({data: this.selections})
                .on('select2:select',  (e) => {
                    this.$store.commit('form/addToSelectedRoomTypes', e.params.data.id);
                })
                .on('select2:unselect', (e) => {
                    this.$store.commit('form/removeFromSelectedRoomTypes', e.params.data.id);
                })
            ;
        }
    }
</script>

<style scoped>

</style>