import {Writer} from "../Writer/Writer";

declare let Routing: Routing;

export abstract class Searcher {
    private button: JQuery;
    private writer: Writer;
    private searchDataReceiver: DataReceiverInterface;

    protected constructor(buttonId: string, writer: Writer, dataReceiver: DataReceiverInterface) {
        this.button = $(`#${buttonId}`);
        this.writer = writer;
        this.searchDataReceiver = dataReceiver;
        this.bindHandlers();
    }

    private bindHandlers(): void {
        this.button.on('click', event => {
            event.preventDefault();
            this.doSpecialSearch();
            this.doSearch();
        })
    }

    protected async abstract doSearch(): Promise<void>;

    private async doSpecialSearch(): Promise<void> {
        let ajax;
        const special_route = Routing.generate('search_specials');
        try {
            ajax = $.ajax({
                url: special_route,
                type: "html",
                data: JSON.stringify(this.getSearchConditions())
            });
            const data = await ajax;
            this.drawSpecialResults(data);
        } catch (e) {
            console.error('Ошибка получеия спец предложений однако');
            console.log(e);
        }
    }

    protected onStartSearch(): void {
        this.writer.showStartSearch();
    }

    protected onStopSearch(requestResults: any): void {
        console.log(requestResults);
        let message: any;
        if(requestResults.status === 'error'){
            message = 'error';
        }
        if(requestResults.status === 'success' && !Object.keys(requestResults.message.results).length) {
            message = 'noResults';
        }

        this.writer.showStopSearch(message);
    }

    protected drawResults(data: any): void {
        const searchResults: SearchResultType[] = data.results;
        this.writer.drawResults(searchResults);
    }
    protected getSearchConditions() {
        return  this.searchDataReceiver.getSearchConditionsData();
    }

    protected drawSpecialResults(data: any): void {
        this.writer.drawSpecialResults(data);
    }



}