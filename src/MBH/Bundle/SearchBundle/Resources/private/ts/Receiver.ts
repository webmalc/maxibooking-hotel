class Receiver {
    private readonly requestThreshold: number = 20;
    private $resultsContainer: JQuery;
    private $routeName: string = 'search_async_results';
    /** TODO: Можно взять Mustache шаблонизатор и фигачить результаты через него */
    private htmlTemplate: string;


    constructor(containerId: string) {
        this.$resultsContainer = $(`#${containerId}`);
    }

    public async receive(conditionsId: string): Promise<void> {
        let count: number = 0;
        let request;
        let error: boolean = false;
        const route = Routing.generate(this.$routeName, {id: conditionsId});
        this.startReceive();
        do {
            request = $.get(route);
            try {
                let data = await request;
                this.writeResults(data.results);
            } catch (err) {
                error = true;
                this.stopReceive(request);
            }
            count++;
            await new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 1000)
            });
        } while (!error && count < this.requestThreshold);

        if (!error) {
            console.log('stop receive by timeout');
        }

    }

    private startReceive(): void {
        console.log('start receive');
    }

    private stopReceive(ajax): void {
        console.log('receive stop with code');
        console.log(ajax.status);
    }

    private writeResults(data: ResultInterface): void {
        console.log('write the results');
        console.log(data);
    }
}