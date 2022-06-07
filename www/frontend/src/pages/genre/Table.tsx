// @flow
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {useEffect, useState} from "react";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import genreHttp from "../../util/http/genre-http";
import {BadgeNo, BadgeYes} from "../../components/Badge";
import {Category, Genre, ListResponse} from "../../util/models";
import DefaultTable, {makeActionStyles, TableColumn} from "../../components/Table";
import {IconButton, MuiThemeProvider} from "@material-ui/core";
import {Link} from "react-router-dom";
import EditIcon from "@material-ui/icons/Edit";
import categoryHttp from "../../util/http/category-http";
import {useSnackbar} from "notistack";

const columnsDefinition: TableColumn[] = [
    {
        name: 'id',
        label: 'ID',
        width: '30%',
        options: {
            sort: false
        }
    },
    {
        name: "name",
        label: "Nome",
        width: '22%',
    },
    {
        name: "categories",
        label: "Categorias",
        width: '24%',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{value.map(value => value.name).join(", ")}</span>;
            }
        }
    },
    {
        name: "is_active",
        label: "Ativo?",
        width: '4%',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value ? <BadgeYes /> : <BadgeNo />;
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em",
        width: '7%',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), "dd/MM/yyyy")}</span>
            }
        }
    },
    {
        name: "actions",
        label: "Ações",
        width: '13%',
        options: {
            sort: false,
            customBodyRender: (value, tableMeta) => {
                return (
                    <IconButton
                        color={'secondary'}
                        component={Link}
                        to={`/genres/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon />
                    </IconButton>
                )
            }
        }
    },
];
type Props = {};
const Table = (props: Props) => {
    const snackbar = useSnackbar();
    const [data, setData] = useState<Genre[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            setLoading(true);
            try {
                const {data} = await genreHttp.list<ListResponse<Genre>>();
                if (isSubscribed) {
                    setData(data.data);
                }
            } catch (error) {
                console.error(error);
                snackbar.enqueueSnackbar('Não foi possível carregar as informações', {variant: 'error'});
            } finally {
                setLoading(false);
            }
        })();
        return () => {
            isSubscribed = false;
        }
    }, []);
    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length-1)}>
            <DefaultTable
                loading={loading}
                title=""
                columns={columnsDefinition}
                data={data}
            />
        </MuiThemeProvider>
    );
};

export default Table;
