class Receiver {
    private $resultsContainer: JQuery;
    private $routeName: string = 'search_async_results';
    /** TODO: Можно взять Mustache шаблонизатор и фигачить результаты через него */
    private htmlTemplate: string;


    constructor(containerId: string) {
        this.$resultsContainer = $(`#${containerId}`);
    }
    public receive(conditionsId: string): void {
        const route = Routing.generate(this.$routeName, {id: conditionsId});

        let func = async (): Promise<number> => {
            try {
                let ajax = $.get(route);
                let data = await ajax;
                console.log(data);
                return ajax.status;
            } catch (e) {
                console.error(e)
            }
        };
        let n:number = 10;

        do {
            let statusCode: number;
            (async () => {
                statusCode = await func();
                console.log('statuscode');
            })();
            n--;
            console.log('problem');
        } while (n >= 0)

    }

    private writeResults(): void {

    }
}