import {Category} from "./category";

describe('Category Unit Tests', () => {
    test('constructor of category', () => {
        // AAA testing

        // ARRANGE
        const props = {
            name: 'Movie',
            description: "description",
            is_active: true,
            created_at: new Date
        }

        // ACT
        const category = new Category(props);

        // ASSERT
        expect(category.name).toBe('Movie');
        expect(category.description).toBe('description');
        expect(category.is_active).toBeTruthy();
        expect(category.created_at).toBe(props.created_at);
    });
});
