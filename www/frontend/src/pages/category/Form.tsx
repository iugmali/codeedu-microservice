// @flow
import * as React from 'react';
import {Box, Button, ButtonProps, Checkbox, FormControlLabel, TextField} from "@material-ui/core";
import {makeStyles, Theme} from "@material-ui/core/styles";
import {useForm} from "react-hook-form";
import categoryHttp from "../../util/http/category-http";
import * as yup from "../../util/vendor/yup";
import {useEffect, useState} from "react";
import {useParams, useHistory} from "react-router-dom";
import {useSnackbar} from "notistack";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

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
        watch
    } = useForm<FormData>({
        validationSchema,
        defaultValues: {
            is_active: true
        }
    });

    const classes = useStyles();
    const { enqueueSnackbar, closeSnackbar } = useSnackbar();
    const history = useHistory();
    const {id} = useParams<{id: string}>();
    const [category, setCategory] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: "secondary",
        disabled: loading
    };

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
        <form onSubmit={handleSubmit(onSubmit)}>
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

            <Box dir={"rtl"}>
                <Button color={"primary"} {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};
