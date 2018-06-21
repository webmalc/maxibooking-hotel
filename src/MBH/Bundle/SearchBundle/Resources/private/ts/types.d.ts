interface SearchDataInterface {
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

interface ResultInterface {

}
