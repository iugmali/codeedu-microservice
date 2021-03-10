// @flow
import * as React from 'react';
import {Box, Button, ButtonProps, Checkbox, FormControlLabel, MenuItem, TextField} from "@material-ui/core";
import {makeStyles, Theme} from "@material-ui/core/styles";
import {useForm} from "react-hook-form";
import genreHttp from "../../util/http/genre-http";
import {useEffect, useState} from "react";
import categoryHttp from "../../util/http/category-http";
import {useSnackbar} from "notistack";
import {useHistory, useParams} from "react-router-dom";
import castMemberHttp from "../../util/http/cast-member-http";
import * as yup from "../../util/vendor/yup";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

const validationSchema = yup.object().shape({
    name: yup.string().label("Nome").required().max(255),
    categories_id: yup.array().label("Categorias").required()
});

type FormData = {
    name: string;
    is_active: boolean;
    categories_id: any[];
}

export const Form = () => {

    const classes = useStyles();

    const {
        register,
        handleSubmit,
        getValues,
        setValue,
        errors,
        watch,
        reset
    } = useForm<FormData>({
        validationSchema,
        defaultValues: {
            is_active: true,
            categories_id: []
        }
    });
    const { enqueueSnackbar, closeSnackbar } = useSnackbar();
    const history = useHistory();
    const {id} = useParams<{id: string}>();
    const [genre, setGenre] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const [categories, setCategories] = useState<any[]>([]);

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
        register({name: "categories_id"})
    }, [register]);

    useEffect(() => {
        (async function loadData() {
            setLoading(true);
            const promises = [categoryHttp.list()];
            if (id) {
                promises.push(genreHttp.get(id));
            }
            try {
                const [categoriesResponse, genreResponse] = await Promise.all(promises);
                setCategories(categoriesResponse.data.data);
                if (id) {
                    setGenre(genreResponse.data.data);
                    reset({
                       ...genreResponse.data.data,
                       categories_id: genreResponse.data.data.categories.map(category => category.id)
                    });
                }
            } catch (error) {
                console.error(error);
                enqueueSnackbar('Não foi possível carregar as informações', {variant: 'error'})
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    async function onSubmit(formData, event) {
        setLoading(true);
        try {
            const http = !genre
                ? genreHttp.create(formData)
                : genreHttp.update(genre.id, formData);
            const {data} = await http;
            enqueueSnackbar('Gênero salvo com sucesso', {
                variant: 'success'
            });
            setTimeout(() => {
                event ? (
                    id
                        ? history.replace(`/genres/${data.data.id}/edit`)
                        : history.push(`/genres/${data.data.id}/edit`)
                ) : history.push('/genres');
            });
        } catch (error) {
            console.error(error);
            enqueueSnackbar('Nao foi possivel salvar o gênero', {
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
                select
                name="categories_id"
                value={watch('categories_id')}
                label="Categorias"
                margin="normal"
                variant={'outlined'}
                fullWidth
                onChange={(e) => {setValue('categories_id', e.target.value as any)}}
                error={errors.categories_id !== undefined}
                helperText={errors.categories_id && errors.categories_id.message}
                SelectProps={{multiple: true}}
                InputLabelProps={{shrink: true}}
                disabled={loading}
            >
                <MenuItem value="" disabled>
                    <em>Selecione Categorias</em>
                </MenuItem>
                {
                    categories.map(
                        (category, key) => (
                            <MenuItem key={key} value={category.id}>{category.name}</MenuItem>
                        )
                    )
                }
            </TextField>
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
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};
