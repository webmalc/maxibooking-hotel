

class Writer {
    private $resultsContainer: JQuery;
    private template: string = "<p>{{roomType}}</p>";
    constructor(resultId: string) {
        this.$resultsContainer = $(`#${resultId}`);
    }

    public showStartSearch(): void {
        console.log('Search started');
    }

    public showStopSearch(): void {
        console.log('Search stopped');
    }

    public drawResults(results: SearchResultType[]): void {

        for (let result of results) {
            let html = this.render(result);
            this.$resultsContainer.append($(html));
        }
        // const drawData = this.sortByRoomType(results);
        // for (let result of drawData) {
        //     let html = this.render(result);
        // }
        // console.log(drawData);
    }

    private render(searchResult: SearchResultType) {
        let view = {
            roomType: searchResult.roomType
        };

        return Mustache.render(this.template, view);
    }

    private viewResult($line: JQuery) {
        this.$resultsContainer.append($line);
    }

    private sortByRoomType(results: SearchResultType[]): {[roomType: string]: SearchResultType[]} {
        let drawData: {[roomType:string]: SearchResultType[]} = {};
        for (let result of results) {
            if (!drawData[result.roomType]) {
                drawData[result.roomType] = [];
            }
            drawData[result.roomType].push(result) ;
        }

        return drawData;
    };
}