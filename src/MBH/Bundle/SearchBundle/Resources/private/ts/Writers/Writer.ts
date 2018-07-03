///<reference path="../Result/RoomTypeHolder.ts"/>
///<reference path="../temp/Wrap.ts"/>
class Writer {
    private holder: RoomTypeHolder;
    private temp: Wrap;

    constructor() {
        this.holder = new RoomTypeHolder();
        this.temp = new Wrap();
        this.init();
    }

    private init(): void {
        Vue.component('search-results', {
            props: ['result'],
            template: '<div>Я тут результат! {{result.type}} {{aloha()}}</div>',
            methods: {
                aloha: function  ()  {
                    return function() {
                        alert('sdf');
                    }
                }
            }

        });
        let app = new Vue({
            el: '#vue_results',
            template: '<span><search-results v-for="result in results" v-bind:result="result" v-bind:key="result.id" ></search-results></span>',
            data: {results: this.temp.getData()}

        });
    }

    public showStartSearch(): void {

    }

    public showStopSearch(): void {

    }

    public drawResults(results: SearchResultType[]): void {
        this.holder.update(results)
    }
}

