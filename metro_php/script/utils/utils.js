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

export function formatTime(time)
{
    var hours = div(time, secondsInHour);
    var minutes = div(time % secondsInHour, secondInMinute);
    var seconds = time - hours * secondsInHour - secondInMinute * minutes;

    return formatTimeVal(hours) + ':' + formatTimeVal(minutes) + ':' + formatTimeVal(seconds);
}