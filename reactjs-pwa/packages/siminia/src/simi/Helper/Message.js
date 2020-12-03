export const showToastMessage = (message, time = '') => {
    if (message) {
        $('#toast-message-content').text(message);
        $('#toast-message-global').show();
        if (time === '') {
            time = message.split(' ').length * 333;
            if (time < 3000) time = 3000;
            if (time > 15000) time = 15000;
        }
        setTimeout(function () {
            $('#toast-message-content').text("");
            $('#toast-message-global').hide();
        }, time);
    }
}