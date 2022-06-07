// @flow
import * as React from 'react';
import {Box, Button, ButtonProps, Checkbox, FormControlLabel, Grid, TextField} from "@material-ui/core";
import {makeStyles, Theme} from "@material-ui/core/styles";
import {useForm} from "react-hook-form";
import categoryHttp from "../../util/http/category-http";
import * as yup from "../../util/vendor/yup";
import {useEffect, useState} from "react";
import {useParams, useHistory} from "react-router-dom";
import {useSnackbar} from "notistack";
import {Category} from "../../util/models";
import SubmitActions from "../../components/SubmitActions";
import DefaultForm from "../../components/DefaultForm";

const validationSchema = yup.object().shape({
    name: yup.string().label("Nome").required().max(255)
});

type FormData = {
    name: string;
    description: string;
    is_active: boolean;
}

export const Form = () => {
    const {
        register,
        handleSubmit,
        getValues,
        setValue,
        errors,
        reset,
        watch,
        triggerValidation
    } = useForm<FormData>({
        validationSchema,
        defaultValues: {
            is_active: true
        }
    });

    const { enqueueSnackbar, closeSnackbar } = useSnackbar();
    const history = useHistory();
    const {id} = useParams<{id: string}>();
    const [category, setCategory] = useState<Category | null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        register({name: "is_active"});
    }, [register]);

    useEffect(() => {
        if (!id) {
            return;
        }
        (async function getCategory() {
            setLoading(true);
            try {
                const {data} = await categoryHttp.get(id);
                setCategory(data.data);
                reset(data.data);
            } catch (error) {
                console.error(error);
                enqueueSnackbar('Não foi possível carregar as informações', {variant: 'error'});
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    async function onSubmit(formData, event) {
        setLoading(true);
        try {
            const http = !category
                ? categoryHttp.create(formData)
                : categoryHttp.update(category.id, formData);
            const {data} = await http;
            enqueueSnackbar('Categoria salva com sucesso', {
                variant: 'success'
            });
            setTimeout(() => {
                event ? (
                    id
                        ? history.replace(`/categories/${data.data.id}/edit`)
                        : history.push(`/categories/${data.data.id}/edit`)
                ) : history.push('/categories');
            });
        } catch (error) {
            console.error(error);
            enqueueSnackbar('Nao foi possivel salvar a categoria', {
                variant: 'error'
            });
        } finally {
            setLoading(false)
        }
    }

    return (
        <DefaultForm GridItemProps={{xs:12, md:6}} onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Nome"
                fullWidth
                variant={"outlined"}
                inputRef={register}
                error={errors.name !== undefined}
                helperText={errors.name && errors.name.message}
                InputLabelProps={{shrink: true}}
                disabled={loading}
            />
            <TextField
                name="description"
                label="Descricao"
                multiline
                rows="4"
                fullWidth
                variant={"outlined"}
                margin={"normal"}
                inputRef={register}
                InputLabelProps={{shrink: true}}
                disabled={loading}
            />
            <FormControlLabel
                control={
                    <Checkbox
                        name="is_active"
                        color={"primary"}
                        onChange={
                            () => setValue('is_active', !getValues()['is_active'])
                        }
                        checked={watch('is_active')}
                    />
                }
                label={"Ativo?"}
                labelPlacement={"end"}
                disabled={loading}
            />
            <SubmitActions handleSave={() => triggerValidation().then(isValid => {isValid && onSubmit(getValues(), null)})} disabledButtons={loading} />
        </DefaultForm>
    );
};
