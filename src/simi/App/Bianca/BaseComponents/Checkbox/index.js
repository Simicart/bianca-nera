import React from 'react'
require ('./index.scss')

export const Checkbox = (props) => {
    return (
        <div 
            {...props}
            className={`checkbox-item ${props.className} ${props.selected?'selected':''}`}
        >
            <div className="checkbox-item-icon" />
            <span className="checkbox-item-text">
                {props.label}
            </span>
        </div>
    )
}
export default Checkbox