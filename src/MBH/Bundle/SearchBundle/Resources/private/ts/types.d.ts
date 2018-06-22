interface SearchDataType {
    begin: string,
    end: string,
    adults: number,
    children?: number,
    childrenAges?: number[],
    additionalBegin?: number,
    additionalEnd?: number,
    roomTypes?: string[],
}

interface Routing {
    generate(name: string, {}?): string;
}

interface SearchResultType {
    id: string,
    prices: {key: string}[],
    rooms: Room[],
    roomType: string

}

interface Room {
    fullTitle: string,
    _id: {$id: string}
}

interface DataReceiverInterface {
    getSearchConditionsData(): SearchDataType;
}
