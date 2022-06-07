// @flow
import * as React from 'react';
import {Box, Button, ButtonProps} from "@material-ui/core";
import {makeStyles, Theme} from "@material-ui/core/styles";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

interface SubmitActionsProps {
    disabledButtons?: boolean;
    handleSave: () => void;
}

const SubmitActions: React.FC<SubmitActionsProps> = (props) => {
    const classes = useStyles();
    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: "secondary",
        disabled: props.disabledButtons === undefined ? false : props.disabledButtons
    };
    return (
        <Box dir={"rtl"}>
            <Button color={"primary"} {...buttonProps} onClick={props.handleSave}>Salvar</Button>
            <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
        </Box>
    );
};

export default SubmitActions;
