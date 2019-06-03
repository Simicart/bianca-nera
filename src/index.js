import React from 'react';
import ReactDOM from 'react-dom';
import { setContext } from 'apollo-link-context';
import { Util, WindowSizeContextProvider } from '@magento/peregrine';

const { BrowserPersistence } = Util;
const apiBase = new URL('/graphql', location.origin).toString();

/**
 * The Siminia adapter provides basic context objects: a router, a store, a
 * GraphQL client, and some common functions. It is not opinionated about auth,
 * so we add an auth implementation here and prepend it to the Apollo Link list.
 */

ReactDOM.render(
    'Hello world',
    document.getElementById('root')
);

if (process.env.SERVICE_WORKER && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register(process.env.SERVICE_WORKER)
            .then(registration => {
                console.log('Service worker registered: ', registration);
            })
            .catch(error => {
                console.log('Service worker registration failed: ', error);
            });
    });
}

window.addEventListener('online', () => {
    store.dispatch(app.setOnline());
});
window.addEventListener('offline', () => {
    store.dispatch(app.setOffline());
});
