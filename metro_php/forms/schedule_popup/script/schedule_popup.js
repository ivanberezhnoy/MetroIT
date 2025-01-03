import * as Utils from '../../../script/utils/utils.js';

function isToday(date) 
{
    const today = new Date();
    
    return date.getFullYear() === today.getFullYear() &&
           date.getMonth() === today.getMonth() &&
           date.getDate() === today.getDate();
}
  
export function loadDateStationSchedule(popupRootShadow, stationID, targetSchedule, scheduleDate, linesInfo, stationsInfo)
{
    var scheduleTableElement = popupRootShadow.getElementById("stationDateSchedule");
    scheduleTableElement.innerHTML = "";

    var stationPointsDirect = [];
    var stationPointsReverse = [];
    var scheduleTime = 0;

    if (popupRootShadow.getElementById("scheduleDate").value != scheduleDate.toISOString().split('T')[0])
    {
        popupRootShadow.getElementById("scheduleDate").value = scheduleDate.toISOString().split('T')[0];
    }    

    if (Utils.isToday(scheduleDate))
    {
        scheduleTime = Utils.getDateSeconds(scheduleDate);
    }

    Object.entries(targetSchedule).forEach(([lineID, lineRouteSchedule]) =>
    {
        var lineStationIndex;
        var lineStations = linesInfo[lineID].stations;
        Object.entries(lineStations).forEach(([index, id]) => 
        {
            if (id == stationID)
            {
                lineStationIndex = index;
                return;
            }
        });

        if (!lineStationIndex)
        {
            return;
        }

        Object.entries(lineRouteSchedule).forEach(([routeID, routeSchedule]) => 
        {
            const stationPointsCount = Object.keys(routeSchedule).length;

            for (var currentStationPoint = 0; currentStationPoint + 1 < stationPointsCount; ++currentStationPoint)
            {
                var currentStationPointInfo = routeSchedule[currentStationPoint];

                if (currentStationPointInfo.lineStationIndex != lineStationIndex)
                {
                    continue;
                }

                var nextStationPointInfo = routeSchedule[currentStationPoint + 1];
                if (currentStationPointInfo.lineStationIndex == nextStationPointInfo.lineStationIndex)
                {
                    continue;
                }

                if (currentStationPointInfo.departure < scheduleTime)
                {
                    continue;
                }

                if (currentStationPointInfo.landingProhibited)
                {
                    continue;
                }

                if (!currentStationPointInfo.departure)
                {
                    console.log(`loadDateStationSchedule: Invalid station point. LineID: ${lineID}, routeID: ${routeID}, stationPointIndex: ${currentStationPoint}, stationPoint: ${currentStationPointInfo}`);
                }

                if (currentStationPointInfo.direction > 0)
                {
                    stationPointsDirect.push({"routeID": routeID, "stationPoint": currentStationPointInfo, "lineID": lineID});
                }
                else
                {
                    stationPointsReverse.push({"routeID": routeID, "stationPoint": currentStationPointInfo, "lineID": lineID});
                }                
            }
        });

    });


    stationPointsDirect.sort((a, b) => a.stationPoint.departure - b.stationPoint.departure);
    stationPointsReverse.sort((a, b) => a.stationPoint.departure - b.stationPoint.departure);

    var anyStationPointInfo;
    if (Object.keys(stationPointsDirect).length > 0)
    {
        anyStationPointInfo = stationPointsDirect[0];
    }
    else if (Object.keys(stationPointsReverse).length > 0)
    {
        anyStationPointInfo = stationPointsReverse[0];
    }

    if (!anyStationPointInfo)
    {
        return;
    }

    const lineID = anyStationPointInfo.lineID;
    const lineInfo = linesInfo[lineID];

    var lineStations = lineInfo.stations;
    const lineStationsCount = Object.keys(lineStations).length;

    popupRootShadow.getElementById("stationName").innerText = `Станція "${stationsInfo[stationID].name}"`;

    var previousStationID;
    var nextStationID;

    Object.entries(lineStations).forEach(([lineStationIndex, lineStationID]) =>
    {
        if (lineStationID == stationID)
        {
            const lineStationIndexInt = Number(lineStationIndex);
            if (lineStationIndexInt > 0)
            {
                previousStationID = `${lineStations[lineStationIndexInt - 1]}`;
            }


            if (lineStationIndexInt + 1 < lineStationsCount)
            {
                nextStationID = `${lineStations[lineStationIndexInt + 1]}`;
            }

        }
    });

    let tableHeader = document.createElement('thead');
    let tableHeaderRow = document.createElement('tr');
    tableHeader.appendChild(tableHeaderRow);

    const directStationPointsCount = Object.keys(stationPointsDirect).length;
    const reverseStationPointsCount = Object.keys(stationPointsReverse).length;

    console.log(`stationID: ${stationID}, nextStationID: ${nextStationID}, previousStationID: ${previousStationID}`);
    const hasDirectStationPoints = directStationPointsCount > 0 && nextStationID && nextStationID <= lineStationsCount;
    if (hasDirectStationPoints)
    {
        let directStationHeader = document.createElement('td');

        directStationHeader.innerText = `На "${stationsInfo[nextStationID].name}"`
        tableHeaderRow.appendChild(directStationHeader);
    }

    const hasReverseStationPoints = reverseStationPointsCount > 0 && previousStationID && previousStationID >= 0;
    if (hasReverseStationPoints)
    {
        let prevStationHeader = document.createElement('td');
        prevStationHeader.innerText = `На "${stationsInfo[previousStationID].name}"`
        tableHeaderRow.appendChild(prevStationHeader);
    }

    let tableBody = document.createElement('tbody');

    var rowsCount = Math.min(Math.max(directStationPointsCount, reverseStationPointsCount), sceduleRowsCount);
    for (var rowIndex = 0; rowIndex < rowsCount; rowIndex++)
    {
        let row = document.createElement('tr');
        if (hasDirectStationPoints)
        {
            let cell = document.createElement('td');
            if (rowIndex < directStationPointsCount)
            {
                cell.innerText = Utils.formatTime(stationPointsDirect[rowIndex].stationPoint.departure);
            }
            row.appendChild(cell);
        }

        if (hasReverseStationPoints)
        {
            let cell = document.createElement('td');
            if (rowIndex < reverseStationPointsCount)
            {
                cell.innerText = Utils.formatTime(stationPointsReverse[rowIndex].stationPoint.departure);
            }
            row.appendChild(cell);
        }        
        tableBody.appendChild(row);
    }
    
    scheduleTableElement.appendChild(tableBody);
    scheduleTableElement.appendChild(tableHeader);

}


const sceduleRowsCount = 10;