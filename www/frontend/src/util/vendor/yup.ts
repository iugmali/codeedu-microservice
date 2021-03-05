import {LocaleObject, setLocale} from "yup";

const ptBR: LocaleObject = {
    mixed: {
        required: "${path} é um campo obrigatório"
    },
    string: {
        max: "${path} precisa ter no máximo ${max} caracteres",
        min: "${path} precisa ter no mínimo ${min} caracteres",
    },
    number: {
        min: "${path} precisa ser no mínimo ${min}",
        max: "${path} precisa ser no máximo ${max}",
    }
}

setLocale(ptBR);

export * from "yup";
