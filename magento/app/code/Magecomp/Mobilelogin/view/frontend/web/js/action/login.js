define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, storage, globalMessageList, customerData, $t) {
    'use strict';

    var callbacks = [],

        action = function (loginData, redirectUrl, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;

            return storage.post(
                'mobilelogin/ajax/login',
                JSON.stringify(loginData),
                isGlobal
            ).done(function (response) {
                    if (response.errors) {
                        messageContainer.addErrorMessage(response);
                        callbacks.forEach(function (callback) {
                            callback(loginData);
                        });
                    } else {
                        callbacks.forEach(function (callback) {
                            callback(loginData);
                        });
                        customerData.invalidate(['customer']);

                        if (response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            location.reload();
                        }
                    }
                }).fail(function () {
                    messageContainer.addErrorMessage({
                        'message': $t('Could not authenticate. Please try again later')
                    });
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                });
        };

    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
