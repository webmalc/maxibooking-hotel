declare let Routing: Routing;
import {Writer} from "../Writer/Writer";
import {Searcher} from "./Searcher";

export class SyncSearcher extends Searcher {

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
            const data: object = await ajax;
            this.drawResults(data);
            this.onStopSearch({status: 'success', message: data});
        } catch (e) {
            this.onStopSearch({status: 'error', message: e});
        }


    }


}