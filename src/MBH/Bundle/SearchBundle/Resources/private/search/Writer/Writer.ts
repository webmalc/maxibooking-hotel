import {FormDataReceiver} from "../Form/FormDataReceiver";

declare let Routing: Routing;

import Vue from 'vue';
import Status from './components/Status/Status.vue';
import SearchResults from './components/Results/SearchResults.vue';

export class Writer {

    private data: any = {};
    public searchStatus: { [index: string]: string } = {state: 'new'};
    private rootApp: any;
    private formDataReceiver: FormDataReceiver;


    constructor(formDataReceiver: FormDataReceiver) {
        this.formDataReceiver = formDataReceiver;
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
            template: '<SearchResults :rawData="rawData" :form="form" />',
            components: {SearchResults},
            data: {
                rawData: this.data,
                form: this.formDataReceiver
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

    public drawResults(receivedData: any): void {
        for (let roomTypeId in receivedData) {
            if (!receivedData.hasOwnProperty(roomTypeId)) {
                continue;
            }
            if (!this.data.hasOwnProperty(roomTypeId)) {
                //  * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.rootApp.$set(this.rootApp.rawData, roomTypeId, receivedData[roomTypeId]);
            } else {
                let existsResults = this.data[roomTypeId].results;
                let newResults = receivedData[roomTypeId].results;
                //В случае с добавлением результатов где ключи списка - даты.
                for (let newResultsKey in newResults) {
                    if (newResults.hasOwnProperty(newResultsKey) && existsResults.hasOwnProperty(newResultsKey)) {
                        for (let newDate of newResults[newResultsKey]) {
                            this.data[roomTypeId].results[newResultsKey].push(newDate);
                        }
                    } else {
                        Vue.set(this.data[roomTypeId].results, newResultsKey, newResults[newResultsKey]);
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