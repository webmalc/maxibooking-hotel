///<reference path="Searchers/AsyncSearcher.ts"/>
///<reference path="Searchers/SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>
///<reference path="vuejs.d.ts"/>
///<reference path="Writers/Writer.ts"/>

let writer = new Writer();

const formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);
