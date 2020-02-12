import React, { useEffect, useRef } from 'react';
import { bool, func, shape, string } from 'prop-types';
import { simiUseQuery } from 'src/simi/Network/Query'

import { mergeClasses } from 'src/classify';
import searchQuery from 'src/simi/queries/catalog/productSearch.graphql'
import Suggestions from './suggestions';
// import Close from 'src/simi/BaseComponents/Icon/TapitaIcons/Close'
import defaultClasses from './searchAutoComplete.css';
import Identify from 'src/simi/Helper/Identify'
import {applySimiProductListItemExtraField} from 'src/simi/Helper/Product'

require('./searchAutoComplete.scss');

function useOutsideAlerter(ref, setVisible) {
    function handleClickOutside(event) {
        if (ref.current && !ref.current.contains(event.target) && setVisible) {
            if (setVisible)
                setVisible(false)
        }
    }

    useEffect(() => {
        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    });
}

  

const SearchAutoComplete = props => {
    const { setVisible, visible, value } = props;

    //handle click outsite
    const wrapperRef = useRef(null);
    useOutsideAlerter(wrapperRef, setVisible);

    //get search result
    const [queryResult, queryApi] = simiUseQuery(searchQuery);
    const { data, error, loading } = queryResult;
    const { resetState, runQuery, setLoading } = queryApi;

    const valid = value && value.length > 2;

    const classes = mergeClasses(defaultClasses, props.classes);
    const rootClassName = visible ? classes.root_visible : classes.root_hidden;
    let message = '';

    if(data)
        data.products = applySimiProductListItemExtraField(data.simiproducts)

    if (error) {
        message = Identify.__('An error occurred while fetching results.');
    } else if (loading) {
        message = Identify.__('Fetching results...');
    } else if (!data) {
        message = Identify.__('Search for a product');
    } else if (!data.products.items.length) {
        message = Identify.__('No results were found.');
    } else {
        message = Identify.__('%s items').replace('%s', data.products.items.length);
    }

    // run the query once on mount, and again whenever state changes
    useEffect(() => {
        if (visible && valid) {
            setLoading(true);
            runQuery({ variables: { inputText: value } });
        } else if (!value) {
            resetState();
        }
    }, [resetState, runQuery, setLoading, valid, value, visible]);

    useEffect(() => {
        if (window.innerWidth < 1024) {
            if (window.location.pathname === "/login.html" || window.location.pathname === "/designer_login.html") {
                if ($('.suggestion-detail').children().length > 0) {
                    if ($('#login-opt-area').length === 1) {
                        $('#login-opt-area').css('display', 'none')
                    }
                } else {
                    if ($('#login-opt-area').length === 1) {
                        $('#login-opt-area').css('display', 'block')
                    }
                }
            }
        }
    });

    return (
        <div className={rootClassName+' search-auto-complete-root'} ref={wrapperRef}>
            
            <div className={classes.message}>
                {message}
                <div role="button" tabIndex="0" className={classes['close-icon']} onClick={() => setVisible(false)} onKeyUp={() => setVisible(false)}>
                    {/* <Close style={{width: 14, height: 14, display: 'block'}} /> */}
                    <i className="icon-cross" />
                </div>
            </div>
            <div className={classes.suggestions}>
                <Suggestions
                    products={data ? data.products : {}}
                    searchValue={value}
                    setVisible={setVisible}
                    visible={visible}
                />
            </div>
        </div>
    );
};

export default SearchAutoComplete;

SearchAutoComplete.propTypes = {
    classes: shape({
        message: string,
        root_hidden: string,
        root_visible: string,
        suggestions: string
    }),
    setVisible: func,
    visible: bool
};
