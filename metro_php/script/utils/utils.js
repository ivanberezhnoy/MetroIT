export const secondInMinute = 60;
export const minutesInHours = 60;
export const secondsInHour = secondInMinute * minutesInHours;

function div(val, by)
{
    return (val - val % by) / by;
}


function formatTimeVal(timeVal)
{
    var res = timeVal.toString();

    if (timeVal < 10)
    {
        res = "0" + res;
    }

    return res;
}

function getHours(seconds)
{
    return div(seconds, secondsInHour);
}

function getMinutes(seconds)
{
    return div(seconds % secondsInHour, secondInMinute);
}

function getSeconds(seconds)
{
    return seconds % secondInMinute;
}

export function formatTime(seconds)
{
    return formatTimeVal(getHours(seconds)) + ':' + formatTimeVal(getMinutes(seconds)) + ':' + formatTimeVal(getSeconds(seconds));
}

export function setDateTime(date, seconds)
{
    date.setHours(getHours(seconds));
    date.setMinutes(getMinutes(seconds));
    date.setSeconds(getSeconds(seconds));
    
    return date;
}

export function isToday(date) 
{
    const today = new Date();
    
    return date.getFullYear() === today.getFullYear() &&
           date.getMonth() === today.getMonth() &&
           date.getDate() === today.getDate();
}

export function getDateSeconds(date)
{
    return date.getHours() * secondsInHour + date.getMinutes() * secondInMinute + date.getSeconds();
}