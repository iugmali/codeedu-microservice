import {Category} from "./category";
import {omit} from "lodash";

describe('Category Unit Tests', () => {
    test('constructor of category', () => {
        // AAA testing - KENT BECK

        // ARRANGE && ACT
        let category = new Category({name: "Movie"});
        let props = omit(category.props, 'created_at');
        // ASSERT
        expect(props).toStrictEqual({
            name: 'Movie',
            description: null,
            is_active: true,
        });
        expect(category.props.created_at).toBeInstanceOf(Date);

        // ARRANGE && ACT
        category = new Category({
            name: "Movie",
            description: "some description",
            is_active: false,
        });
        let created_at = new Date();
        // ASSERT
        expect(category.props).toStrictEqual({
           name: "Movie",
           description: "some description",
           is_active: false,
           created_at,
        });

        // ARRANGE && ACT
        category = new Category({
            name: "Movie2",
            description: "other description",
        })
        // ASSERT
        expect(category.props).toMatchObject({
            name: "Movie2",
            description: "other description",
        })

        // ARRANGE && ACT
        category = new Category({
            name: "Movie2",
            is_active: true,
        })
        // ASSERT
        expect(category.props).toMatchObject({
            name: "Movie2",
            is_active: true,
        })

        // ARRANGE && ACT
        created_at = new Date();
        category = new Category({
            name: "Movie2",
            created_at,
        })
        // ASSERT
        expect(category.props).toMatchObject({
            name: "Movie2",
            created_at,
        })
    });
});
