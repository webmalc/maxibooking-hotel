<template>
    <transition
            enter-active-class="animated fadeInUp"
            leave-active-class="animated fadeOutDown"
            mode="out-in"
            :duration="{enter:100, leave: 100}"
            appear
    >
    <div v-html="specials" v-if="specials"></div>
    </transition>
</template>

<script>
    export default {
        name: "Specials",
        computed: {
            specials() {
                return this.$store.state.results.specialsHtml
            }
        },
        updated() {
            console.log(this.$el);
            // let $specialWrapper = $holder.find('#package-new-search-special-wrapper');
            // $specialWrapper.readmore({
            //     moreLink: '<div class="more-link"><a href="#">'+$specialWrapper.attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
            //     lessLink: '<div class="less-link"><a href="#">'+$specialWrapper.attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
            //     collapsedHeight: 230
            // });
            let $holder = $(this.$el);
            let $specialTouristSelect = $holder.find('.search-special-tourist-select');
            let $specialPrice = $holder.find('.special-price');
            let $specialLinks = $holder.find('a.booking-special-apply');
            $specialTouristSelect.select2({
                placeholder: '',
                allowClear: false,
                width: 'element'
            }).on('change.select2', function () {
                let html = $(this).val();
                $(this).closest('td').siblings('td').find('span.special-price').html(html);
            });
            $.each($specialPrice, function () {
                let html = $(this).closest('td').siblings('td').find('select.search-special-tourist-select').val();
                $(this).html(html);
            });
            $specialLinks.on('click', function (event) {
                event.preventDefault();
                let relatedSelect = $(this).closest('td').siblings('td').find('select.search-special-tourist-select option:selected');
                let linkAdults = relatedSelect.data('adults');
                let linkChildren = relatedSelect.data('children');
                let bookingUrl = Routing.generate('special_booking', {
                    'id': $(this).data('id'),
                    'adults': linkAdults,
                    'children': linkChildren
                });
                window.open(bookingUrl);
            });
        }
    }
</script>

<style scoped>
    .fade-enter-active{
        transition: opacity .1s;
    }
    .fade-leave-active {
        transition: opacity .1s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>