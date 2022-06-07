// @flow
import * as React from 'react';
import {IconButton, Tooltip} from "@material-ui/core";
import ClearAllIcon from "@material-ui/icons/ClearAll";
import {makeStyles} from "@material-ui/core/styles";

const useStyles = makeStyles(theme => ({
    iconButton: (theme as any).overrides.MUIDataTableToolbar.icon
}));
interface FilterResetButtonProps {
    handleClick: () => void
}
export const FilterResetButton: React.FC<FilterResetButtonProps> = (props) => {
    const classes = useStyles();
    return (
        <Tooltip title={"Limpar Busca"}>
            <IconButton>
                <ClearAllIcon className={classes.iconButton} onClick={props.handleClick} />
            </IconButton>
        </Tooltip>
    );
};
