import {FormDataReceiver} from "./Form/FormDataReceiver";
import {Writer} from "./Writer/Writer";
import {AsyncSearcher} from "./Searchers/AsyncSearcher";
import {SyncSearcher} from "./Searchers/SyncSearcher";

let writer = new Writer();
let formDataReceiver = new FormDataReceiver('search_conditions');

new AsyncSearcher('searcher-submit-button', writer, formDataReceiver);
new SyncSearcher('searcher-sync-submit-button', writer, formDataReceiver);