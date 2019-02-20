// import {FormDataReceiver} from "./Form/FormDataReceiver";
// import {Writer} from "./Writer/Writer";
// import {AsyncSearcher} from "./Searchers/AsyncSearcher";
// import {SyncSearcher} from "./Searchers/SyncSearcher";
//
// let formDataReceiver = new FormDataReceiver('search_conditions');
// let writer = new Writer(formDataReceiver);
//
// new AsyncSearcher('searcher-submit-button', writer, formDataReceiver);
// new SyncSearcher('searcher-sync-submit-button', writer, formDataReceiver);

import {Root} from "./root/Root";

const root = new Root();
root.startApplication();