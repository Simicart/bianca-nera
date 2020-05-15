import React from 'react'

const Logo = props => {
    const {className, style} = props;
    return (
        <svg className={className || 'logo logo-group'} version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
	        viewBox="0 0 255.12 71.57" style={style}>
            <path d="M5.9,38a6,6,0,0,0,3-.89,12.6,12.6,0,0,0,2.9-2.41,23.5,23.5,0,0,0,2.58-3.4,29.59,29.59,0,0,0,2.08-3.93,26.31,26.31,0,0,0,1.37-4,14.9,14.9,0,0,0,.5-3.58,3,3,0,0,0-.9-2.35,3.58,3.58,0,0,0-2.45-.8,5.44,5.44,0,0,0-2.85.88,13.29,13.29,0,0,0-2.8,2.3,21.5,21.5,0,0,0-2.56,3.3A27.31,27.31,0,0,0,4.73,27a26.92,26.92,0,0,0-1.4,4,14.52,14.52,0,0,0-.53,3.68c0,2.24,1,3.35,3.1,3.35m.9-16.21.1.06a19,19,0,0,1,1.48-2,13.86,13.86,0,0,1,1.93-1.9,11.89,11.89,0,0,1,2.27-1.43,6.07,6.07,0,0,1,2.58-.57,6.23,6.23,0,0,1,2.73.57,6.32,6.32,0,0,1,2,1.5,6.22,6.22,0,0,1,1.2,2.13,7.79,7.79,0,0,1,.4,2.5,11.1,11.1,0,0,1-.75,4,16.93,16.93,0,0,1-2,3.75,21.77,21.77,0,0,1-2.85,3.31,21.89,21.89,0,0,1-3.35,2.62A18.17,18.17,0,0,1,9,38a9.46,9.46,0,0,1-3.23.63,6.4,6.4,0,0,1-2-.33,5.42,5.42,0,0,1-1.88-1,5.31,5.31,0,0,1-1.4-1.8A5.93,5.93,0,0,1,0,32.77a10.17,10.17,0,0,1,.32-2.32,17,17,0,0,1,.73-2.33L10.8.7H6.4L6.6,0h8Z"/>
            <path d="M35.4,3.08a2.4,2.4,0,0,1,3.35,0,2.25,2.25,0,0,1,.67,1.67,2.35,2.35,0,1,1-4.7,0,2.26,2.26,0,0,1,.68-1.67M27.16,17.26l.21-.7h8l-5.9,17c-.17.5-.34,1-.5,1.53a4.69,4.69,0,0,0-.25,1.48,1.52,1.52,0,0,0,.35,1,1.28,1.28,0,0,0,1.05.42,2.71,2.71,0,0,0,1.57-.54,8.67,8.67,0,0,0,1.43-1.28,9.29,9.29,0,0,0,1.1-1.48,11.23,11.23,0,0,0,.65-1.2l.6.25q-.2.41-.63,1.23a7.62,7.62,0,0,1-1.17,1.65A7.07,7.07,0,0,1,31.77,38a5.37,5.37,0,0,1-2.6.6A4.55,4.55,0,0,1,26.54,38a2.58,2.58,0,0,1-1-2.28,6.4,6.4,0,0,1,.31-1.87c.2-.69.4-1.31.6-1.88l5.15-14.66Z"/>
            <path d="M57.31,20.84a4.64,4.64,0,0,0,.18-1.18,4.4,4.4,0,0,0-.13-1,2.65,2.65,0,0,0-.45-1,2.67,2.67,0,0,0-2.32-1.1,6.26,6.26,0,0,0-3.23.93,12.75,12.75,0,0,0-3,2.42,21.15,21.15,0,0,0-2.53,3.43,31.11,31.11,0,0,0-2,3.9,26,26,0,0,0-1.27,3.83,14,14,0,0,0-.45,3.25A4.79,4.79,0,0,0,42.8,37a2.26,2.26,0,0,0,2.08,1,5.69,5.69,0,0,0,2.95-.89,14.31,14.31,0,0,0,2.75-2.16,20.79,20.79,0,0,0,2.15-2.5,8.48,8.48,0,0,0,1.15-2l2.65-7.35a22.24,22.24,0,0,0,.78-2.28M56.16,34.22a8.18,8.18,0,0,0-.32,2.15c0,.87.41,1.31,1.25,1.31a3,3,0,0,0,1.8-.58,6.79,6.79,0,0,0,1.37-1.3,8.57,8.57,0,0,0,1-1.45l.53-1,.65.35c-.17.37-.42.84-.75,1.4a7.75,7.75,0,0,1-1.3,1.63,8.23,8.23,0,0,1-1.85,1.35,5.07,5.07,0,0,1-2.45.58q-3.32,0-3.31-3a8.49,8.49,0,0,1,.15-1.22,10.39,10.39,0,0,1,.4-1.58h-.1c-.46.57-1,1.19-1.6,1.88a14.63,14.63,0,0,1-2,1.9,11.35,11.35,0,0,1-2.48,1.45,7.64,7.64,0,0,1-3,.58,5.62,5.62,0,0,1-2-.38,4.81,4.81,0,0,1-1.73-1.15,5.29,5.29,0,0,1-1.17-2,8.63,8.63,0,0,1-.43-2.85,10.59,10.59,0,0,1,.81-4,18.72,18.72,0,0,1,2.07-3.83,20.9,20.9,0,0,1,2.95-3.37A27.44,27.44,0,0,1,48,18.36a18.17,18.17,0,0,1,3.41-1.8,8.42,8.42,0,0,1,3-.65,4,4,0,0,1,2.56.85,2.76,2.76,0,0,1,1,2.25H58l.85-2.45h3.5L56.94,31.72c-.3.87-.56,1.7-.78,2.5"/>
            <path d="M68.14,17.26l.2-.7h7.91l-2,6.16h.1c3.23-4.31,6.29-6.46,9.15-6.46a4.7,4.7,0,0,1,3.33,1.13A3.6,3.6,0,0,1,88,20.16a5.38,5.38,0,0,1-.28,1.68c-.18.55-.41,1.21-.67,2L83.8,33.07c-.2.57-.38,1.13-.55,1.68A5.8,5.8,0,0,0,83,36.37a2.58,2.58,0,0,0,.25,1.11,1.06,1.06,0,0,0,1,.49A3,3,0,0,0,86,37.48a7.43,7.43,0,0,0,1.37-1.21,8.41,8.41,0,0,0,1.08-1.47c.3-.52.53-1,.7-1.33l.55.25c-.17.33-.4.78-.7,1.33a8.92,8.92,0,0,1-1.18,1.63A6.69,6.69,0,0,1,86,38.05a5.13,5.13,0,0,1-2.5.58,4.91,4.91,0,0,1-2.7-.71,2.51,2.51,0,0,1-1.1-2.3,5.46,5.46,0,0,1,.18-1.35c.11-.46.25-1,.42-1.5l4.2-12a6.5,6.5,0,0,0,.4-2,2.16,2.16,0,0,0-.42-1.25A1.76,1.76,0,0,0,83,17a5.25,5.25,0,0,0-2.55.83A14.23,14.23,0,0,0,77.5,20a20.68,20.68,0,0,0-2.73,3.35,19.16,19.16,0,0,0-2.07,4.18L68.94,38H65.49L72.7,17.26Z"/>
            <path d="M108.29,35.05a11.07,11.07,0,0,1-1.9,1.77,9.06,9.06,0,0,1-2.47,1.3,9.23,9.23,0,0,1-3.16.51,8.59,8.59,0,0,1-3.3-.6A7,7,0,0,1,95,36.4,7.2,7.2,0,0,1,93.53,34a7.94,7.94,0,0,1-.52-2.9,12.76,12.76,0,0,1,1.2-5.23,18.9,18.9,0,0,1,3.2-4.9A17.51,17.51,0,0,1,102,17.34a10.88,10.88,0,0,1,5.33-1.43,7.2,7.2,0,0,1,1.67.2,4.42,4.42,0,0,1,1.48.65A3.61,3.61,0,0,1,111.55,18a4,4,0,0,1,.42,1.93c0,1.1-.27,1.81-.82,2.14a3.25,3.25,0,0,1-1.73.51,1.67,1.67,0,0,1-1.25-.38,1.21,1.21,0,0,1-.35-.88,2.09,2.09,0,0,1,.2-1,3.29,3.29,0,0,1,.47-.68A2.92,2.92,0,0,0,109,19a2.13,2.13,0,0,0,.2-1,1.12,1.12,0,0,0-.58-1.1,3.4,3.4,0,0,0-1.57-.3,6,6,0,0,0-3,.8,10.1,10.1,0,0,0-2.58,2.13,15.28,15.28,0,0,0-2.08,3,28,28,0,0,0-1.55,3.5,22.13,22.13,0,0,0-1,3.55,17.47,17.47,0,0,0-.33,3.2,5.62,5.62,0,0,0,1.23,3.66A4.57,4.57,0,0,0,101.52,38a6.21,6.21,0,0,0,2.57-.52,9.27,9.27,0,0,0,2.13-1.32,11.64,11.64,0,0,0,1.65-1.73c.46-.62.85-1.18,1.15-1.68l.7.4a16.79,16.79,0,0,1-1.43,1.93"/>
            <path d="M131.41,20.84a5.07,5.07,0,0,0,.17-1.18,4.35,4.35,0,0,0-.12-1,3,3,0,0,0-.45-1,2.77,2.77,0,0,0-.88-.77,2.8,2.8,0,0,0-1.45-.33,6.25,6.25,0,0,0-3.22.93,12.79,12.79,0,0,0-3,2.42A21.11,21.11,0,0,0,120,23.39a31.11,31.11,0,0,0-2,3.9,26.08,26.08,0,0,0-1.28,3.83,14,14,0,0,0-.45,3.25,4.79,4.79,0,0,0,.63,2.6A2.26,2.26,0,0,0,119,38a5.71,5.71,0,0,0,3-.89,14.62,14.62,0,0,0,2.75-2.16,20.79,20.79,0,0,0,2.15-2.5,8.87,8.87,0,0,0,1.15-2l2.65-7.35q.6-1.61.78-2.28m-1.15,13.38a8.23,8.23,0,0,0-.33,2.15c0,.87.42,1.31,1.25,1.31a3,3,0,0,0,1.8-.58,6.6,6.6,0,0,0,1.38-1.3,9.22,9.22,0,0,0,.95-1.45l.53-1,.65.35c-.17.37-.42.84-.75,1.4a8.09,8.09,0,0,1-1.3,1.63,8.3,8.3,0,0,1-1.86,1.35,5,5,0,0,1-2.45.58q-3.3,0-3.3-3A8.49,8.49,0,0,1,127,34.4a9.34,9.34,0,0,1,.4-1.58h-.1c-.47.57-1,1.19-1.6,1.88a14.71,14.71,0,0,1-2,1.9,11.05,11.05,0,0,1-2.47,1.45,7.66,7.66,0,0,1-3,.58,5.57,5.57,0,0,1-2-.38,4.73,4.73,0,0,1-1.73-1.15,5.29,5.29,0,0,1-1.17-2,8.38,8.38,0,0,1-.43-2.85,10.77,10.77,0,0,1,.8-4,19.27,19.27,0,0,1,2.08-3.83,20.43,20.43,0,0,1,3-3.37,27.44,27.44,0,0,1,3.4-2.71,17.87,17.87,0,0,1,3.41-1.8,8.37,8.37,0,0,1,3-.65,4,4,0,0,1,2.55.85A2.76,2.76,0,0,1,132,19h.1l.85-2.45h3.51L131,31.72c-.3.87-.56,1.7-.77,2.5"/>
            <path d="M156.41,37V16.93H153.1v-.7h9.31v5h.1c.13-.3.37-.75.7-1.37A7.36,7.36,0,0,1,164.66,18a8.84,8.84,0,0,1,2.45-1.57,9.16,9.16,0,0,1,3.76-.68,6.78,6.78,0,0,1,5.12,1.9,7.21,7.21,0,0,1,1.83,5.21V37h3.35v.69H168.61V37h3.11V21.18a4.63,4.63,0,0,0-.85-3.07,2.92,2.92,0,0,0-2.31-1,4.85,4.85,0,0,0-2.3.58,5.56,5.56,0,0,0-1.92,1.72A9.41,9.41,0,0,0,163,22.28a14.45,14.45,0,0,0-.5,4V37h3.15v.69H153.1V37Z"/>
            <path d="M199,21.08c0-.36,0-.81-.05-1.35a5.48,5.48,0,0,0-.35-1.6,3.16,3.16,0,0,0-1-1.35,2.94,2.94,0,0,0-1.88-.55,3.94,3.94,0,0,0-2.63.8,4.82,4.82,0,0,0-1.37,2,10.15,10.15,0,0,0-.58,2.65c-.08.95-.14,1.86-.17,2.72h8Zm-8,6.31c0,1.2.05,2.41.15,3.62a10.11,10.11,0,0,0,.8,3.31,6,6,0,0,0,1.9,2.42,5.62,5.62,0,0,0,3.5.95,7,7,0,0,0,3.38-.75A8.25,8.25,0,0,0,203,35.22a7.61,7.61,0,0,0,1.3-1.93,5.87,5.87,0,0,0,.48-1.4l.7.25a9.1,9.1,0,0,1-.85,2,7.85,7.85,0,0,1-1.66,2,8.75,8.75,0,0,1-2.6,1.55,10,10,0,0,1-3.75.62,14.72,14.72,0,0,1-4.85-.77,11.73,11.73,0,0,1-3.93-2.25,10.55,10.55,0,0,1-2.63-3.6,11.79,11.79,0,0,1-.95-4.83,10.3,10.3,0,0,1,.9-4.2,12,12,0,0,1,2.48-3.63,12.58,12.58,0,0,1,3.7-2.55,10.7,10.7,0,0,1,4.58-1,11.51,11.51,0,0,1,3.5.55,9.25,9.25,0,0,1,3.15,1.7,8.8,8.8,0,0,1,2.31,3,9.83,9.83,0,0,1,.9,4.38H191Z"/>
            <path d="M218.85,16.23v4.2h.1a10.32,10.32,0,0,1,2.42-3.27,5.78,5.78,0,0,1,4-1.38,5,5,0,0,1,3.4,1.13A3.9,3.9,0,0,1,230.1,20a3.56,3.56,0,0,1-.72,2.36,2.94,2.94,0,0,1-2.33.85,4.57,4.57,0,0,1-2.1-.48A1.81,1.81,0,0,1,224,21c0-.09,0-.27.05-.52s.07-.52.13-.8.09-.55.12-.78,0-.36,0-.4a2.08,2.08,0,0,0-.15-.62,1,1,0,0,0-.33-.4,1,1,0,0,0-.67-.18,2.5,2.5,0,0,0-1.25.48,5.17,5.17,0,0,0-1.4,1.37,9.09,9.09,0,0,0-1.13,2.18,8.13,8.13,0,0,0-.47,2.88V37h4v.71H209.59V37h3.25V16.93h-3.25v-.7Z"/>
            <path d="M244.91,25.29a1.51,1.51,0,0,1-.37.57,4,4,0,0,1-1,.63c-.63.3-1.27.59-1.9.87a7.23,7.23,0,0,0-1.7,1.08,5.18,5.18,0,0,0-1.23,1.6,5.57,5.57,0,0,0-.47,2.45c0,.4,0,.87.07,1.4a5.21,5.21,0,0,0,.4,1.55,3.41,3.41,0,0,0,1,1.28,2.78,2.78,0,0,0,1.8.52,3,3,0,0,0,1.28-.3,3.21,3.21,0,0,0,1.12-.92,4.7,4.7,0,0,0,.8-1.65,9,9,0,0,0,.3-2.48v-6.6Zm8.18,12.86a17.07,17.07,0,0,1-2.47.2,6.32,6.32,0,0,1-3.78-.91,3.84,3.84,0,0,1-1.53-2.5h-.1a4.76,4.76,0,0,1-2.2,2.7,9.22,9.22,0,0,1-4,.71,12.23,12.23,0,0,1-2.45-.26,8.31,8.31,0,0,1-2.31-.8,4.6,4.6,0,0,1-1.7-1.52,4.21,4.21,0,0,1-.65-2.38,4.74,4.74,0,0,1,.58-2.42,5.4,5.4,0,0,1,1.5-1.66,8.71,8.71,0,0,1,2.1-1.1c.78-.28,1.59-.56,2.43-.82,1.36-.4,2.48-.74,3.33-1a8,8,0,0,0,2-.9,2.73,2.73,0,0,0,1-1.07,3.83,3.83,0,0,0,.27-1.56v-3a4.68,4.68,0,0,0-.65-2.45,2.61,2.61,0,0,0-2.45-1.1,3.73,3.73,0,0,0-2,.53,2.07,2.07,0,0,0-.85,1.92,3.51,3.51,0,0,0,.05.53c0,.25.07.52.1.82s.07.59.1.85.05.45.05.55a1.82,1.82,0,0,1-.35,1.18,2.29,2.29,0,0,1-.83.63,3,3,0,0,1-1,.22c-.35,0-.64,0-.87,0a7.23,7.23,0,0,1-1-.07,2.13,2.13,0,0,1-.92-.35,2.09,2.09,0,0,1-.68-.78,2.84,2.84,0,0,1-.27-1.35,3.94,3.94,0,0,1,.72-2.32,5.7,5.7,0,0,1,1.93-1.7,10.11,10.11,0,0,1,2.72-1,14.41,14.41,0,0,1,3.18-.35,20.33,20.33,0,0,1,3.58.3,7.79,7.79,0,0,1,2.9,1.1,5.72,5.72,0,0,1,1.95,2.18,7.42,7.42,0,0,1,.73,3.48V35a4.77,4.77,0,0,0,.27,1.68c.19.48.64.72,1.38.72a2,2,0,0,0,1.07-.25,3.29,3.29,0,0,0,.83-.7l.45.46a2.88,2.88,0,0,1-2,1.25"/>
            <path d="M146.7,27a1.65,1.65,0,1,0-1.64,1.64A1.64,1.64,0,0,0,146.7,27"/>
            <path d="M98.43,68.1h0a3.42,3.42,0,0,1,3.47-3.48,3.6,3.6,0,0,1,2.58.91l-.74.89a2.65,2.65,0,0,0-1.89-.72,2.28,2.28,0,0,0-2.18,2.38v0a2.25,2.25,0,0,0,2.29,2.4,2.72,2.72,0,0,0,1.62-.51V68.72h-1.72v-1h2.87v2.83a4.36,4.36,0,0,1-2.81,1,3.34,3.34,0,0,1-3.49-3.47"/>
            <path d="M114.94,68.06c.84,0,1.38-.44,1.38-1.13v0c0-.72-.52-1.11-1.39-1.11h-1.72v2.26ZM112,64.72h3a2.75,2.75,0,0,1,2,.67,2.08,2.08,0,0,1,.55,1.46v0a2,2,0,0,1-1.6,2l1.81,2.55h-1.39l-1.65-2.34h-1.48v2.34H112Z"/>
            <path d="M130.23,68.1h0a2.31,2.31,0,0,0-2.28-2.4,2.28,2.28,0,0,0-2.27,2.38v0A2.31,2.31,0,0,0,128,70.48a2.27,2.27,0,0,0,2.26-2.38m-5.79,0h0A3.44,3.44,0,0,1,128,64.61a3.41,3.41,0,0,1,3.5,3.46v0a3.52,3.52,0,0,1-7,0"/>
            <path d="M138.56,68.6V64.72h1.18v3.83c0,1.25.65,1.92,1.7,1.92s1.7-.63,1.7-1.87V64.72h1.18v3.82a2.69,2.69,0,0,1-2.89,3,2.65,2.65,0,0,1-2.87-3"/>
            <path d="M154.26,68.22c.9,0,1.45-.5,1.45-1.2v0c0-.79-.56-1.2-1.45-1.2h-1.37v2.42Zm-2.56-3.5h2.66A2.26,2.26,0,0,1,156.91,67v0c0,1.51-1.21,2.3-2.69,2.3h-1.33v2.16H151.7Z"/>
        </svg>
    );
}
export default Logo