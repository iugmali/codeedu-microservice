// @flow
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn, MUIDataTableOptions, MUIDataTableProps} from "mui-datatables";
import {merge, omit, cloneDeep} from 'lodash';
import {MuiThemeProvider, useMediaQuery, useTheme} from "@material-ui/core";
import {Theme} from "@material-ui/core/styles";
import DebouncedTableSearch from "./DebouncedTableSearch";
export interface TableColumn extends MUIDataTableColumn {
    width?: string;
}
const makeDefaultOptions = (debouncedSearchTime?): MUIDataTableOptions => ({
    print: false,
    download: false,
    textLabels: {
        body: {
            noMatch: "Nenhum registro encontrado",
            toolTip: "Classificar"
        },
        pagination: {
            next: "Próxima página",
            previous: "Página anterior",
            rowsPerPage: "Por página:",
            displayRows: "de"
        },
        toolbar: {
            search: "Busca",
            downloadCsv: "Download CSV",
            print: "Imprimir",
            viewColumns: "Ver Colunas",
            filterTable: "Filtrar Tabela"
        },
        filter: {
            all: "Todos",
            title: "FILTROS",
            reset: "LIMPAR"
        },
        viewColumns: {
            title: "Ver Colunas",
            titleAria: "Ver/Esconder Colunas da Tabela"
        },
        selectedRows: {
            text: "registro(s) selecionado(s)",
            delete: "Excluir",
            deleteAria: "Excluir registros selecionados"
        }
    },
    customSearchRender: (searchText: string,
                         handleSearch: any,
                         hideSearch: any,
                         options: any) => {
        return <DebouncedTableSearch
            searchText={searchText}
            onSearch={handleSearch}
            onHide={hideSearch}
            options={options}
            debounceTime={debouncedSearchTime}
        />
    }
});
export interface TableProps extends MUIDataTableProps {
    columns: TableColumn[];
    loading?: boolean;
    debouncedSearchTime?: number;
}
const Table: React.FC<TableProps> = (props) => {
    function extractMuiDatatableColumns(columns: TableColumn[]): MUIDataTableColumn[] {
        setColumnsWidth(columns);
        return columns.map(column => omit(column, 'width'));
    }
    function setColumnsWidth(columns: TableColumn[]) {
        columns.forEach(column => {
            if (column.width) {
                const overrides = theme.overrides as any;
                overrides.MUIDataTableHeadCell.fixedHeader['&:nth-child(${key+2})'] = {
                    width: column.width
                }
            }
        })
    }
    function applyLoading() {
        const textLabels = (newProps.options as any).textLabels;
        textLabels.body.noMatch = newProps.loading === true
            ? "Carregando..."
            : textLabels.body.noMatch;
    }
    function applyResponsive() {
        newProps.options.responsive = isSmOrDown ? 'standard' : 'vertical';
    }
    function getOriginalMuiDataTableProps() {
        return omit(newProps, 'loading');
    }
    const theme = cloneDeep<Theme>(useTheme());
    const isSmOrDown = useMediaQuery(theme.breakpoints.down('sm'));
    const defaultOptions = makeDefaultOptions(props.debouncedSearchTime);
    const newProps = merge({options: cloneDeep(defaultOptions)}, props, {columns: extractMuiDatatableColumns(props.columns)});
    applyLoading();
    applyResponsive();
    const originalProps = getOriginalMuiDataTableProps();
    return (
        <MuiThemeProvider theme={theme}>
            <MUIDataTable {...originalProps}/>
        </MuiThemeProvider>
    );
};
export default Table;
export function makeActionStyles(column) {
    return theme => {
        const copyTheme = cloneDeep(theme);
        const selector = `&[data-testid^="MuiDataTableBodyCell-${column}"]`;
        (copyTheme.overrides as any).MUIDataTableBodyCell.root[selector] = {
            paddingTop: '0px',
            paddingBottom: '0px'
        };
        return copyTheme;
    }
}
