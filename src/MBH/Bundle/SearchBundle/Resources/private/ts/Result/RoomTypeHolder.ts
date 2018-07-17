class RoomTypeHolder {
    private roomTypes: RoomType[] = [];

    public update(results) {
        for (let roomTypeKey in results) {
            if (!this.isRoomTypeExists(roomTypeKey)) {
                let roomType = new RoomType(results[roomTypeKey]);
                this.roomTypes.push(roomType);
            }
        }
    }

    public getData(): RoomType[] {
        return this.roomTypes;
    }

    private isRoomTypeExists(key: string): boolean {
        for (let roomType of this.roomTypes) {
            if (roomType.getId() === key) {
                return true;
            }
        }

        return false;
    }
}