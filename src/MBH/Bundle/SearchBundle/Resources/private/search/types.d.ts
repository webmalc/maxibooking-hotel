interface SearchDataType {
    begin: string,
    end: string,
    adults: number,
    children?: number,
    childrenAges?: any,
    additionalBegin?: number,
    additionalEnd?: number,
    roomTypes?: any,
    tariffs?: any,
    hotels?: any,
    order?: number,
    isForceBooking?: boolean|number,
    isSpecialStrict?: boolean,
    isUseCache?: boolean
}

interface Routing {
    generate(name: string, {}?): string;
}

interface SearchResultType {

}

interface Room {
    fullTitle: string,
    _id: {$id: string}
}

interface DataReceiverInterface {
    getSearchConditionsData(): SearchDataType;
}