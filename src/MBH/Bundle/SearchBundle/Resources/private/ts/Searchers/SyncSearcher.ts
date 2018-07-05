class SyncSearcher extends Searcher {

    public constructor(buttonId: string, writer: Writer, dataReceiver: DataReceiverInterface) {
        super(buttonId, writer, dataReceiver);
    }

    protected async doSearch(): Promise<void> {
        this.onStartSearch();
        let ajax;
        const start_route = Routing.generate('search_sync_start_json', {grouping: 'roomType'});
        try {
            ajax = $.ajax({
                url: start_route,
                type: "POST",
                dataType: "json",
                data: JSON.stringify(this.getSearchConditions())
            });
            const data = await ajax;
            this.drawResults(data);
            this.onStopSearch({status: 'success'});
        } catch (e) {
            this.onStopSearch(e);
        }


    }


}