export function loadSchedule(stationID, targetSchedule, linesInfo)
{
    var stationPoints = [];
    Object.entries(targetSchedule).forEach((lineID, lineRouteSchedule) =>
    {
        Object.entries(lineRouteSchedule).forEach((routeID, routeSchedule) => 
        {
            const stationPointsCount = Object.keys(routeSchedule).length;

            for (var currentStationPoint = 0; currentStationPoint + 1 < stationPointsCount; ++stationPointsCount)
            {
                var currentStationPointInfo = routeSchedule[currentStationPoint];

                if (currentStationPointInfo.lineStationIndex != lineID)
                {
                    continue;
                }

                var nextStationPointInfo = routeSchedule[currentStationPoint + 1];

                if (currentStationPointInfo.lineStationIndex == nextStationPointInfo.lineStationIndex)
                {
                    continue;
                }

                
            }
        });
    });
}