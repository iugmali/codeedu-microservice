import {RouteProps} from "react-router-dom";
import Dashboard from "../pages/Dashboard";
import CategoryList from "../pages/category/PageList";
import GenreList from "../pages/genre/PageList";
import CastMemberList from "../pages/cast-member/PageList";

export interface MyRouteProps extends RouteProps{
    name: string;
    label: string;
}

const routes: MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    {
        name: 'categories.list',
        label: 'Listar Categorias',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'categories.create',
        label: 'Criar Categoria',
        path: '/categories/create',
        component: CategoryList,
        exact: true
    },
    {
        name: 'genres.list',
        label: 'Listar Generos',
        path: '/genres',
        component: GenreList,
        exact: true
    },
    {
        name: 'genres.create',
        label: 'Criar Genero',
        path: '/genres/create',
        component: GenreList,
        exact: true
    },
    {
        name: 'cast_members.list',
        label: 'Listar Membros de Elencos',
        path: '/cast-members',
        component: CastMemberList,
        exact: true
    },
    {
        name: 'cast_members.create',
        label: 'Criar Membro de Elenco',
        path: '/cast-members/create',
        component: CastMemberList,
        exact: true
    },
];

export default routes;
