class RoomType {
    private id: string;

    private dateResults: SearchResultType

    private results: Result[] = [];

    constructor(result: SearchResultType) {
        this.id = result.roomType.id;
    }

    public getId(): string {
        return this.id;
    }

    public update(result: SearchResultType): void {

    }
}