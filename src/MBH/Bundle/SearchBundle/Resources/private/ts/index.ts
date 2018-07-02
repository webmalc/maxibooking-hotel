///<reference path="Writers/Writer.ts"/>
///<reference path="Searchers/AsyncSearcher.ts"/>
///<reference path="Searchers/SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>


const writer = new Writer('results');
const formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);




