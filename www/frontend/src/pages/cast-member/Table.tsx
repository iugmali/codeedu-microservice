// @flow
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {useEffect, useState} from "react";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import castMemberHttp from "../../util/http/cast-member-http";
import {CastMember, Category, ListResponse} from "../../util/models";
import DefaultTable, {makeActionStyles, TableColumn} from "../../components/Table";
import {IconButton, MuiThemeProvider} from "@material-ui/core";
import {useSnackbar} from "notistack";
import {Link} from "react-router-dom";
import EditIcon from "@material-ui/icons/Edit";

const CastMemberTypes = {
    1: "Diretor",
    2: "Ator"
}
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
        width: '39%',
    },
    {
        name: "type",
        label: "Tipo",
        width: '8%',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{CastMemberTypes[value]}</span>;
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em",
        width: '10%',
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
                        to={`/cast-members/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon />
                    </IconButton>
                )
            }
        }
    }
];
type Props = {};
const Table = (props: Props) => {
    const snackbar = useSnackbar();
    const [data, setData] = useState<CastMember[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            setLoading(true);
            try {
                const {data} = await castMemberHttp.list<ListResponse<CastMember>>();
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
