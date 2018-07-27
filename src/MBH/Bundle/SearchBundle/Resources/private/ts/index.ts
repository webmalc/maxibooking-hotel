///<reference path="Searchers/AsyncSearcher.ts"/>
///<reference path="Searchers/SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>
///<reference path="vuejs.d.ts"/>
///<reference path="Writers/Writer.ts"/>
///<reference path="../../../../../../../node_modules/moment/moment.d.ts"/>


let writer = new Writer();

const formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('searcher-submit-button', writer, formDataReceiver);
new SyncSearcher('searcher-sync-submit-button', writer, formDataReceiver);
