import * as Utils from './utils/utils.js';
import * as UIUTils from './utils/ui_utils.js';

function processPromiseResult(promise)
{
  return promise.then(response => 
    {
      if (!response.ok) 
      {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      return response.text(); // Если ошибки PHP включены, они будут в тексте
    })
    .then(data => {
      try {
        const jsonData = JSON.parse(data);
        return jsonData;
      } 
      catch (e) 
      {
        console.error('Ошибка на сервере:', data);
      }
    })
    .catch(error => {
      console.error('Ошибка сети или запроса:', error);
    });  
}

function loadRoutes()
{
  const selectRouteCombo = document.getElementById("RouteSelection");

  processPromiseResult(fetch('./api/api.php?action=getRoutes')).then(jsonData =>
  {
    routes = jsonData;

    Object.keys(jsonData).forEach(function(routeID) 
    {
        const routeOption = document.createElement("option");
        routeOption.value = jsonData[routeID];
        routeOption.text = jsonData[routeID];
  
        selectRouteCombo.add(routeOption);
    });
  }
  );
}

function loadSchedule()
{
  processPromiseResult(fetch('./api/api.php?action=getRoutesSchedule')).then(jsonData =>
    {
      schedule = jsonData;
    }
    );  
}

function loadStations()
{
  processPromiseResult(fetch('./api/api.php?action=getStations')).then(jsonData =>
    {
      stations = jsonData;
    }
    );  
}

function clearScheduleTable()
{
    const scheduleTable = document.getElementById('scheduleTable');
    scheduleTable.innerHTML = "";
}

function printRoutePage(routeSchedule, pageIndex, minStationID, maxStationID)
{
    const travelPointsCount = routeSchedule.length;

    let firstFinishPointIndex = -1;

    for (let currentPointIndex = 0; currentPointIndex < travelPointsCount; currentPointIndex++)
    {
        let travelPoint = routeSchedule[currentPointIndex];
        if (travelPoint.station === minStationID && travelPoint.direction < 0)
        {
            firstFinishPointIndex = currentPointIndex;

            break;
        }
    }

    if (firstFinishPointIndex === -1)
    {
        console.log("Error: Unable to find finish point");

        return false;
    }

    const stationsCount = 2 * (maxStationID - minStationID + 1); 

    console.log("stationsCount: " + stationsCount);

    let finishPointIndex = pageIndex * stationsCount + firstFinishPointIndex;
    let startPointIndex = finishPointIndex - stationsCount  + 1;

    if (startPointIndex >= travelPointsCount)
    {
        console.log("Error: Invalid route page index");
        return false;
    }


    const scheduleTable = document.getElementById('scheduleTable');
    scheduleTable.style = "border: 1px solid black;border-collapse: collapse;";


    for (let stationID = minStationID; stationID <= maxStationID; stationID++)
    {
        var row = UIUTils.addTableRow(scheduleTable, scheduleTable.rows.length);

        var startTime = UIUTils.addRowCell(row, 0);
        var startRemarks = UIUTils.addRowCell(row, 1);
        startRemarks.style = "width:100px;"

        var stationName = UIUTils.addRowCell(row, 2);
        stationName.innerHTML = stations[stationID].name;
        
        var endRemarks = UIUTils.addRowCell(row, 3);
        endRemarks.style = "width:100px;"
        var endTime = UIUTils.addRowCell(row, 4);

        if (startPointIndex >= 0 && startPointIndex < travelPointsCount)
        {
            var startPointInfo = routeSchedule[startPointIndex];

            startTime.innerHTML = Utils.formatTime(startPointInfo.station == maxStationID ? routeSchedule[startPointIndex].arrival : routeSchedule[startPointIndex].departure);
        }

        startPointIndex++;

        if (finishPointIndex < travelPointsCount && finishPointIndex >= 0)
        {
            var finishPointInfo = routeSchedule[finishPointIndex];
            endTime.innerHTML = Utils.formatTime(finishPointInfo.station == minStationID ? routeSchedule[finishPointIndex].arrival : routeSchedule[finishPointIndex].departure);
        }

        finishPointIndex--;
    }
}

function reloadRoutePage()
{
    clearScheduleTable();

    const selectRouteCombo = document.getElementById("RouteSelection");
    const selectedRouteValue = parseInt(selectRouteCombo.value);

    var pageSelectionCombo = document.getElementById("PageSelection");
    const selectedPageValue = parseInt(pageSelectionCombo.value);

    console.log("reloadRoutePage");
    if (selectedRouteValue > 0 && selectedPageValue >= 0 && schedule[selectedRouteValue] != undefined)
    {
        const startStationID = 1;
        const finishStationID = Object.keys(stations).length;
        printRoutePage(schedule[selectedRouteValue], selectedPageValue, startStationID, finishStationID);
    }
}

function handleRouteSelection()
{
  const selectRouteCombo = document.getElementById("RouteSelection");
  const selectedRouteValue = parseInt(selectRouteCombo.value);

  clearScheduleTable();

  document.getElementById("PageOptions").hidden = true;

  var pageSelectionCombo = document.getElementById("PageSelection");

  while(pageSelectionCombo.options.length > 1)
  {
      pageSelectionCombo.remove(1);
  }
  
  if (selectedRouteValue > 0) 
  {
      var routeSchedule = schedule[selectedRouteValue];

      if (routeSchedule == null || routeSchedule == undefined)
      {
          return;
      }

      const startStationID = 1;
      const finishStationID = Object.keys(stations).length;

      var pagesCount = 0;
      for (const travelPoint of routeSchedule)
      {
          if (travelPoint.station == startStationID && travelPoint.direction > 0)
          {
              ++pagesCount;
          }
      }

      if (routeSchedule[0].station != startStationID || routeSchedule[0].direction < 0)
      {
          ++pagesCount;
      }

      
      for (let pageIndex = 0; pageIndex < pagesCount; ++pageIndex)
      {
          const pageOption = document.createElement("option");
          pageOption.value = pageIndex;
          pageOption.text = pageIndex + 1;

          pageSelectionCombo.add(pageOption);
      }

      document.getElementById("PageOptions").hidden = false;

      reloadRoutePage();
  }   
}

function handlePageSelection()
{
    reloadRoutePage();
}


document.addEventListener("DOMContentLoaded", () => 
  {
    loadRoutes();
    loadStations();
    loadSchedule();

    document.getElementById('RouteSelection').addEventListener('change', handleRouteSelection);
    document.getElementById('PageSelection').addEventListener('change', handlePageSelection);

    const output = document.getElementById('output');

    // Обработчик для "Get User"
    // document.getElementById('getUser').addEventListener('click', () => 
    //   {
    //     fetch('api.php?action=getRoutesSchedule')
    //     .then(response => 
    //       {
    //         if (!response.ok) 
    //         {
    //           throw new Error(`HTTP error! Status: ${response.status}`);
    //         }
    //         return response.text(); // Если ошибки PHP включены, они будут в тексте
    //       })
    //       .then(data => {
    //         try {
    //           const jsonData = JSON.parse(data);
    //           output.innerHTML = data;
    //         } catch (e) {
    //           console.error('Ошибка на сервере:', data);
    //         }
    //       })
    //       .catch(error => {
    //         console.error('Ошибка сети или запроса:', error);
    //       });
    // });

    // // Обработчик для "Create User"
    // document.getElementById('createUser').addEventListener('click', () => 
    //   {
    //     const userData = { name: "Bob", age: 30 };

    //     fetch('api.php?action=createUser', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json'
    //         },
    //         body: JSON.stringify(userData)
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             output.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //             output.textContent = 'Error creating user';
    //         });
    // });

    // // Обработчик для "Delete User"
    // document.getElementById('deleteUser').addEventListener('click', () => {
    //     const formData = new FormData();
    //     formData.append('action', 'deleteUser');
    //     formData.append('id', 1); // Пример ID пользователя

    //     fetch('api.php', {
    //         method: 'POST',
    //         body: formData
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             output.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //             output.textContent = 'Error deleting user';
    //         });
    // });
});

var schedule = {};
var stations = {};
var routes = {};