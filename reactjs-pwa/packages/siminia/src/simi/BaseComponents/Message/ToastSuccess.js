import React from 'react';
const ToastSuccess = props => {
    const hideToast = () =>{
        window.$('#toast-success-global').hide();
        window.$('#toast-success-content').text("");
    }

    return (
        <div id="toast-success-global" style={{display: 'none'}}>
            <div id="toast-success-content" role="presentation" onClick={hideToast} />
        </div>
    );
}
export default ToastSuccess
