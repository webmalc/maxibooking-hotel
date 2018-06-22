class SyncSearcher extends Searcher {

    public constructor(buttonId: string, writer: Writer, dataReceiver: DataReceiverInterface) {
        super(buttonId, writer, dataReceiver);
    }

    protected async doSearch(): Promise<void> {
        let ajax;
        const start_route = Routing.generate('search_sync_start_json');
        try {
            ajax = $.ajax({
                url: start_route,
                type: "POST",
                dataType: "json",
                data: JSON.stringify(this.getSearchConditions())
            });
            const data = await ajax;
            this.drawResults(data);
        } catch (e) {
            this.onStopSearch(e);
        }


    }


}