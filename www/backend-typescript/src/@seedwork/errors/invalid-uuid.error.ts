export default class InvalidUuidError extends Error {
    constructor(message?: string) {
        super('ID must be a valid uuid');
        this.name = 'InvalidUuidError'
    }
}
