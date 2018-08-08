declare let Routing: Routing;

abstract class Searcher {
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
            this.doSearch();
        })
    }

    protected async abstract doSearch(): Promise<void>;

    protected onStartSearch(): void {
        this.writer.showStartSearch();
    }

    protected onStopSearch(requestResults: any): void {
        console.log(requestResults);
        this.writer.showStopSearch();
    }

    protected drawResults(data): void {
        const searchResults: SearchResultType[] = data.results;
        this.writer.drawResults(searchResults);
    }
    protected getSearchConditions() {
        return  this.searchDataReceiver.getSearchConditionsData();

    }

}