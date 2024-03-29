
export const getFormattedDate = (data, lang='en') => {
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    //let dated = Date.parse(data); 
    // console.log(data);
    const t = data.split(/[- :]/);
    // Apply each element to the Date function
    const d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

    const date = new Date(d);
    const dd = date.getDate();
    /* switch (dd){
        case 1:
            dd = dd + 'st'
            break;
        case 2:
            dd = dd + 'nd'
            break;
            case 3:
            dd = dd + 'rd'
            break;
        default:
            dd = dd + 'th'
            break;
    } */
    const yy = date.getFullYear();
    if (lang === 'ar') {
        const months = ["يناير", "فبراير", "مارس", "إبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];
        const numberMap = { 0: '٠', 1: '١', 2: '٢', 3: '٣', 4: '٤', 5: '٥', 6: '٦', 7: '٧', 8: '٨', 9: '٩' };
        const translate = (number) => {
            const numStr = String(number);
            const len = numStr.length;
            let arNum = '';
            for(let i=0; i<len; i++){
                arNum += numberMap[numStr.charAt(i)];
            }
            return arNum;
        }
        return translate(yy) + '-' + months[date.getMonth()] + '-' + translate(dd);
    }
    return dd + ' ' + monthNames[date.getMonth()] + ' ' + yy;
}