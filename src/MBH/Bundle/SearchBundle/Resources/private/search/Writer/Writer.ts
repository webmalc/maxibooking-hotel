declare let Routing: Routing;

import Vue from 'vue';
import Status from './components/Status/Status.vue';
import SearchResults from './components/Results/SearchResults.vue';

export class Writer {

    private data: any = {};
    public searchStatus: { [index: string]: string } = {state: 'new'};
    private rootApp: any;


    constructor() {
        this.init();
    }

    private init() {
        this.searchVueInit();
        this.showSearchStatusInit();
    }

    private showSearchStatusInit(): void {
        new Vue({
            el: '#package-searcher-results-wrapper',
            template: '<Status v-bind:status="status" />',
            components: {Status},
            data: {
                status: this.searchStatus
            }
        })
    }

    private searchVueInit(): void {
        this.rootApp = new Vue({
            el: '#search_results',
            template: '<SearchResults v-bind:rawData="rawData" />',
            components: {SearchResults},
            data: {
                rawData: this.data
            }
        })
    }

    public showStartSearch(): void {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
        this.searchStatus.state = 'process';
    }

    public showStopSearch(state: string): void {
        console.log('Search stopped');
        this.searchStatus.state = state;
    }

    public drawResults(data: any): void {
        for (let newKey in data) {
            if (!data.hasOwnProperty(newKey)) {
                continue;
            }
            if (!this.data.hasOwnProperty(newKey)) {
                //  * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.rootApp.$set(this.rootApp.rawData, newKey, data[newKey]);
            } else {
                let existsDates = this.data[newKey].results;
                let newDates = data[newKey].results;
                for (let newDatesKey in newDates) {
                    if (newDates.hasOwnProperty(newDatesKey) && existsDates.hasOwnProperty(newDatesKey)) {
                        for (let newDate of newDates[newDatesKey]) {
                            this.data[newKey].results[newDatesKey].push(newDate);
                        }
                    } else {
                        Vue.set(this.data[newKey].results, newDatesKey, newDates[newDatesKey]);
                    }
                }
            }
        }
    }

    public drawSpecialResults(data: any): void {
        let $holder = $('#specials');
        $holder.empty();
        $holder.append($(data));
        // let $specialWrapper = $holder.find('#package-new-search-special-wrapper');
        // $specialWrapper.readmore({
        //     moreLink: '<div class="more-link"><a href="#">'+$specialWrapper.attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
        //     lessLink: '<div class="less-link"><a href="#">'+$specialWrapper.attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
        //     collapsedHeight: 230
        // });
        let $specialTouristSelect = $holder.find('.search-special-tourist-select');
        let $specialPrice = $holder.find('.special-price');
        let $specialLinks = $holder.find('a.booking-special-apply');
        $specialTouristSelect.select2({
            placeholder: '',
            allowClear: false,
            width: 'element'
        }).on('change.select2', function () {
            const html: any = $(this).val();
            $(this).closest('td').siblings('td').find('span.special-price').html(html);
        });
        $.each($specialPrice, function () {
            const html: any = $(this).closest('td').siblings('td').find('select.search-special-tourist-select').val();
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