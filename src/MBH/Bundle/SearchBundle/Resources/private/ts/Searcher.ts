declare let Routing: Routing;

class Searcher {
    private route: string = Routing.generate('search_start_json');
    private button: JQuery;
    private asyncReceiver: Receiver;

    constructor(buttonId: string) {
        this.button = $(`#${buttonId}`);
        this.asyncReceiver = new Receiver('results');
        this.init();
    }

    private init(): void {
        this.bindHandlers();
    }

    private bindHandlers(): void {
        this.button.on('click', event => {
            event.preventDefault();
            this.startSearch();
        })
    }




    private startSearch(): void {
        (async () => {
            try {
                const data = await this.sendSearchData();
                console.log(data);
                this.asyncReceiver.receive(data.conditionsId);
            } catch (e) {
                console.log(e);
            }
        })();
    }
    private async sendSearchData(): Promise<{conditionsId: string}> {
        return $.ajax({
            url: this.route,
            type: "POST",
            dataType: "json",
            data: JSON.stringify(this.getData())
        });
    }

    private getData(): SearchDataInterface {
        let data: SearchDataInterface;
        data = {
            begin: '05.08.2018',
            end: '12.08.2018',
            adults: 2
        };

        return data;
    }

}