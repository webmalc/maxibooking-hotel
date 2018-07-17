class Inner {
    private type: string;

    private number: number;


    constructor(type: string, number: number) {
        this.type = type;
        this.number = number;
    }

    public getType(): string {
        return this.type;
    }

    public getNumber(): number {
        return this.number;
    }
}