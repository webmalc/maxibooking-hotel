///<reference path="Inner.ts"/>
class Wrap {
    private inners: Inner[] = [];


    constructor() {
        this.inners.push(new Inner('first inner', 1 ));
        this.inners.push(new Inner('second inner', 2));
    }

    public getData(): Inner[] {
        return this.inners;
    }
}