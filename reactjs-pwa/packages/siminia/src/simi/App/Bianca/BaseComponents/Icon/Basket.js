
import React from 'react'
import {defaultStyle} from './Consts'

const Basket = props => {
    const color = props.color ? {fill: props.color} : {};
    const style = {...defaultStyle, ...props.style, ...color}

    return (
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
            viewBox="0 0 20 20"
            className={props.className}
            style={style}>
            <title>Basket</title>
            <g id="Combined-Shape">
                <path d="M19.997 19.438l-0.998-13.974c-0.019-0.262-0.236-0.464-0.499-0.464h-2.293l-3.854-3.854c-0.195-0.195-0.512-0.195-0.707 0l-0.5 0.5c-0.195 0.195-0.195 0.512 0 0.707s0.512 0.195 0.707 0l0.146-0.146 2.793 2.793h-1.586l-4.354-4.354c-0.195-0.195-0.512-0.195-0.707 0l-4.354 4.354h-2.293c-0.262 0-0.48 0.203-0.499 0.464l-1 14c-0.010 0.138 0.038 0.275 0.133 0.376s0.227 0.159 0.366 0.159h19c0 0 0 0 0.001 0 0.276 0 0.5-0.224 0.5-0.5 0-0.021-0.001-0.041-0.004-0.062zM8.5 1.707l3.293 3.293h-6.586l3.293-3.293zM1.037 19l0.929-13h2.034c0 0 0 0 0 0h14.034l0.929 13h-17.926z" fill="#000000"></path>
                <path d="M10 14c-1.103 0-2.127-0.596-2.884-1.678-0.719-1.028-1.116-2.385-1.116-3.822 0-0.276 0.224-0.5 0.5-0.5s0.5 0.224 0.5 0.5c0 1.234 0.332 2.388 0.935 3.249 0.565 0.807 1.298 1.251 2.065 1.251s1.5-0.444 2.065-1.251c0.603-0.861 0.935-2.015 0.935-3.249 0-0.276 0.224-0.5 0.5-0.5s0.5 0.224 0.5 0.5c0 1.437-0.396 2.795-1.116 3.822-0.757 1.082-1.782 1.678-2.884 1.678z" fill="#000000"></path>
            </g>
        </svg>
    )
}
export default Basket