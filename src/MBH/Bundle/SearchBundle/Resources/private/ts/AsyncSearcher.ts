///<reference path="Searcher.ts"/>
class AsyncSearcher extends Searcher {

    private readonly requestThreshold: number = 2;

    public constructor(buttonId: string, writer: Writer, dataReceiver: DataReceiverInterface) {
        super(buttonId, writer, dataReceiver);
    }

    protected async doSearch(): Promise<void> {
        this.onStartSearch();
        const start_route = Routing.generate('search_start_async');
        let ajax;
        try {
            ajax = $.ajax({
                url: start_route,
                type: "POST",
                dataType: "json",
                data: JSON.stringify(this.getSearchConditions())
            });
            const conditionsResults = await ajax;
            let count: number = 0;
            let requestResults;
            let error: boolean = false;
            const resultRoute = Routing.generate('search_async_results', {id: conditionsResults.conditionsId});
            do {
                requestResults = $.get(resultRoute);
                try {
                    const data = await requestResults;
                    this.drawResults(data);
                } catch (err) {
                    error = true;
                    this.onStopSearch(requestResults);
                }
                count++;
                await new Promise((resolve) => {
                    setTimeout(() => {
                        resolve();
                    }, 1000)
                });
            } while (!error && count < this.requestThreshold);
            if (!error) {
                console.log('Stop async receive by threshold.');
            }
        } catch (e) {
            this.onStopSearch(ajax);
        }
    }
}