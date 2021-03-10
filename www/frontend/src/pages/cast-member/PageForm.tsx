// @flow
import * as React from 'react';
import {Page} from "../../components/Page";
import {Form} from "./Form";
import {useParams} from "react-router-dom";

const PageForm = () => {
    const {id} = useParams<{id: string}>();
    return (
        <Page title={!id ? 'Criar Membro de Elenco' : 'Editar Membro de Elenco'}>
            <Form />
        </Page>
    );
};

export default PageForm;
