import React, {useEffect, useState} from 'react';
import Grow from '@material-ui/core/Grow';
import TextField from '@material-ui/core/TextField';
import SearchIcon from '@material-ui/icons/Search';
import IconButton from '@material-ui/core/IconButton';
import ClearIcon from '@material-ui/icons/Clear';
import { makeStyles } from '@material-ui/core/styles';
import {useDebouncedCallback} from "use-debounce";

const useStyles = makeStyles(
    theme => ({
        main: {
            display: 'flex',
            flex: '1 0 auto',
        },
        searchIcon: {
            color: theme.palette.text.secondary,
            marginTop: '10px',
            marginRight: '8px',
        },
        searchText: {
            flex: '0.8 0',
        },
        clearIcon: {
            '&:hover': {
                color: theme.palette.error.main,
            },
        },
    }),
    { name: 'MUIDataTableSearch' },
);

const DebouncedTableSearch = ({ options, searchText, onSearch, onHide, debounceTime }) => {
    let value = searchText;
    if (searchText && searchText.value !== undefined) {
        value = searchText.value;
    }
    const [text, setText] = useState(value);
    const classes = useStyles();

    const dispatchOnSearch = useDebouncedCallback(
        (value) => {onSearch(value)},debounceTime
    );

    const handleTextChange = event => {
        const value = event.target.value;
        setText(value);
        dispatchOnSearch(value);
    };

    const onKeyDown = event => {
        if (event.key === 'Escape') {
            onHide();
        }
    };

    return (
        <Grow appear in={true} timeout={300}>
            <div className={classes.main}>
                <SearchIcon className={classes.searchIcon} />
                <TextField
                    className={classes.searchText}
                    autoFocus={true}
                    InputProps={{
                        'data-test-id': options.textLabels.toolbar.search,
                    }}
                    inputProps={{
                        'aria-label': options.textLabels.toolbar.search,
                    }}
                    value={text || ''}
                    onKeyDown={onKeyDown}
                    onChange={handleTextChange}
                    fullWidth={true}
                    placeholder={options.searchPlaceholder}
                    {...(options.searchProps ? options.searchProps : {})}
                />
                <IconButton className={classes.clearIcon} onClick={onHide}>
                    <ClearIcon />
                </IconButton>
            </div>
        </Grow>
    );
};

function areEqual(prevProps, nextProps) {
    return prevProps.searchText && prevProps.searchText.value !== undefined && prevProps.searchText === nextProps.searchText;
}

export default React.memo(DebouncedTableSearch, areEqual);
