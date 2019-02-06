<template>
    <select class="plain-html" multiple="multiple"></select>
</template>

<script>
    const data = [
        {
            id: 0,
            text: 'enhancement'
        },
        {
            id: 1,
            text: 'bug'
        },
        {
            id: 2,
            text: 'duplicate'
        },
        {
            id: 3,
            text: 'invalid'
        },
        {
            id: 4,
            text: 'wontfix'
        }
    ];

    export default {
        name: "RoomTypeInput",
        data() {
            return {
                selected: []
            }
        },
        mounted() {
            $(this.$el).select2({data: data})
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