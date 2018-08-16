<template>
    <small>
        <i v-popover class="fa fa-question-circle" data-container="body" data-toggle="popover"
           data-placement="left" data-html="true"
           :data-content="detail"
        ></i>
    </small>
</template>

<script type="ts">
    export default {
        name: "DayPrice",
        props: ['dayPrices'],
        computed: {
            detail: function () {
                let html: string = '';
                for (let dayPrice of this.dayPrices) {
                    html += `${dayPrice['day']} - ${dayPrice['price']} - <i class='fa fa-sliders'></i> ${dayPrice['tariff']['name']}<br>`;
                }

                return `<small>${html}</small>`;
            }
        },
        directives: {
            popover: {
                inserted: function (el) {
                    $(el).popover();
                }
            }
        }
    }
</script>

<style scoped>

</style>