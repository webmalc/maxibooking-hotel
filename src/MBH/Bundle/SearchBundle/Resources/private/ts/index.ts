///<reference path="Writer.ts"/>
///<reference path="AsyncSearcher.ts"/>
///<reference path="SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>


const writer = new Writer('results');
const formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);




