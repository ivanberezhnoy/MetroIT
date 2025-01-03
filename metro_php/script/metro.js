import * as Utils from './utils/utils.js';
import * as UIUTils from './utils/ui_utils.js';
import * as API from './api.js';
import * as Geometry from "./utils/Geometry.js"
import * as SVG from "./utils/svg_utils.js"
import * as SchedulePopup from "../forms/schedule_popup/script/schedule_popup.js"

function loadRoutes()
{
  const selectRouteCombo = document.getElementById("RouteSelection");

  API.loadRoutes().then(jsonData =>
  {
    routes = jsonData;

    Object.keys(routes).forEach(function(routeID) 
    {
        if (routes[routeID]["lineID"] == selectedLineID)
        {
            const routeOption = document.createElement("option");
            routeOption.value = routeID;
            routeOption.text = routeID;
    
            selectRouteCombo.add(routeOption);
        }
    });
  }
  );
}

function loadLines()
{
    API.loadLines().then(jsonData => {
        lines = jsonData;
      });
}

function loadSchedule()
{
  API.loadSchedule().then(jsonData => {
    schedule = jsonData;
  });
}

function loadStations()
{
  API.loadStations().then(jsonData => {
    stations = jsonData;
  });
}

function clearScheduleTable()
{
    const scheduleTable = document.getElementById('scheduleTable');
    scheduleTable.innerHTML = "";
}

function printRoutePage(routeSchedule, pageIndex, line)
{
    const travelPointsCount = routeSchedule.length;

    let firstFinishPointIndex = -1;

    const minStationIndex = 0;
    const maxStationIndex = Object.keys(line['stations']).length - 1;

    for (let currentPointIndex = 0; currentPointIndex < travelPointsCount; currentPointIndex++)
    {
        let travelPoint = routeSchedule[currentPointIndex];
        if (travelPoint.lineStationIndex === minStationIndex && travelPoint.direction < 0)
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

    const stationsCount = 2 * (maxStationIndex - minStationIndex + 1); 

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


    for (let stationIndex = minStationIndex; stationIndex <= maxStationIndex; stationIndex++)
    {
        var row = UIUTils.addTableRow(scheduleTable, scheduleTable.rows.length);

        var startTime = UIUTils.addRowCell(row, 0);
        var startRemarks = UIUTils.addRowCell(row, 1);
        startRemarks.style = "width:100px;"

        var stationName = UIUTils.addRowCell(row, 2);
        stationName.innerHTML = stations[line['stations'][stationIndex]].name;
        
        var endRemarks = UIUTils.addRowCell(row, 3);
        endRemarks.style = "width:100px;"
        var endTime = UIUTils.addRowCell(row, 4);

        if (startPointIndex >= 0 && startPointIndex < travelPointsCount)
        {
            var startPointInfo = routeSchedule[startPointIndex];
            
            // Show arrival time if is finial station or schedule end
            const useArrivalTime = startPointInfo.isFinalStation != undefined || startPointIndex == travelPointsCount - 1;

            startTime.innerHTML = Utils.formatTime(useArrivalTime ? routeSchedule[startPointIndex].arrival : routeSchedule[startPointIndex].departure);
        }

        startPointIndex++;

        if (finishPointIndex < travelPointsCount && finishPointIndex >= 0)
        {
            var finishPointInfo = routeSchedule[finishPointIndex];

            // Show arrival time if is finial station or schedule end
            const useArrivalTime = finishPointInfo.isFinalStation != undefined || finishPointIndex == travelPointsCount - 1;

            endTime.innerHTML = Utils.formatTime(useArrivalTime ? routeSchedule[finishPointIndex].arrival : routeSchedule[finishPointIndex].departure);
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

    if (selectedRouteValue > 0 && selectedPageValue >= 0 && schedule[selectedLineID][selectedRouteValue] != undefined)
    {
        printRoutePage(schedule[selectedLineID][selectedRouteValue], selectedPageValue, lines[selectedLineID]);
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
      var routeSchedule = schedule[selectedLineID][selectedRouteValue];

      if (routeSchedule == null || routeSchedule == undefined)
      {
          return;
      }

      const startStationIndex = 1;

      var pagesCount = 0;
      for (const travelPoint of routeSchedule)
      {
          if (travelPoint.lineStationIndex == startStationIndex && travelPoint.direction > 0)
          {
              ++pagesCount;
          }
      }

      if (routeSchedule[0].lineStationIndex != startStationIndex || routeSchedule[0].direction < 0)
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

function updateTrainsPositions()
{
    currentSeconds = Utils.getDateSeconds(new Date());

    if (svgElement == null)
    {
        console.log("Unable to find schema SVG");
        return;
    }

    if (arrowSvgElement == null || arrowSvgElement == undefined)
    {
        console.log("Unable to find arrow");
        return;
    }

    if (schedule && lines)
    {
        Object.entries(schedule).forEach(([leneID, lineSchedule]) => 
        {
            Object.entries(lineSchedule).forEach(([routeID, routeSchedule]) => 
            {
                const stationPointCount = Object.keys(routeSchedule).length;
                var stationPointIndex = 0;

                for (; stationPointIndex + 2 < stationPointCount; ++stationPointIndex)
                {
                    var nextStationPointInfo = routeSchedule[stationPointIndex + 1];
                    if (nextStationPointInfo.arrival != undefined && currentSeconds < nextStationPointInfo.arrival)
                    {
                        break;
                    }
                }

                // Route is not started yet
                if (stationPointIndex == 0 && routeSchedule[stationPointIndex].departute > currentSeconds)
                {
                    return;
                }

                // Route is finished
                if (stationPointIndex + 2 == stationPointCount && routeSchedule[stationPointCount - 1]["arrival"] <= currentSeconds)
                {
                    var routeArrow = routesArrowSVG[routeID];
                    
                    if (routeArrow != undefined)
                    {
                        svgElement.parentNode.removeChild(svgElement);

                        delete routesArrowSVG.routeID;
                    }


                    return;
                }

                // Станция на которой находится сейчас или последняя которую проехал
                const currentStationInfo = routeSchedule[stationPointIndex];
                const currentStationID = lines[leneID]["stations"][currentStationInfo["lineStationIndex"]];
                // Следующая станци
                const nextStationInfo = routeSchedule[stationPointIndex + 1];
                const nextStationID = lines[leneID]["stations"][nextStationInfo["lineStationIndex"]];

                var routeArrow = routesArrowSVG[routeID];
                if (routeArrow == undefined)
                {
                    routeArrow = arrowSvgElement.cloneNode(true);
                    svgElement.appendChild(routeArrow);

                    routesArrowSVG[routeID] = routeArrow;
                }

                const startStationBBox = SVG.getSVGElementRect(svgElement, SVG.getStationPointID(currentStationID));
                const endStationBBox = SVG.getSVGElementRect(svgElement, SVG.getStationPointID(nextStationID));
                const arrowSvgBBox = new Geometry.Rect(routeArrow.getBBox());
            
                if (!startStationBBox || !endStationBBox || !arrowSvgBBox)
                {
                    console.log("Unable to calculate train position");
                    return 
                }
                
                const startStationCenter = startStationBBox.getCenter();
                const endStationCenter = endStationBBox.getCenter();
                const arrowCenter = arrowSvgBBox.getCenter();
            
                
                var moveVector = endStationCenter.subtract(startStationCenter);

                //Разворот на конечной станции
                if (currentStationID == nextStationID)
                {
                    if (stationPointIndex > 0)
                    {
                        const prevStationInfo = routeSchedule[stationPointIndex - 1];
                        const prevStationID = lines[leneID]["stations"][prevStationInfo["lineStationIndex"]];

                        const prevStationCenter = SVG.getSVGElementRect(svgElement, SVG.getStationPointID(prevStationID)).getCenter();

                        moveVector = startStationCenter.subtract(prevStationCenter);
                        if (currentStationInfo.departure < currentSeconds)
                        {
                            const rotateFraction = (currentSeconds - currentStationInfo.departure) / (nextStationInfo.arrival - currentStationInfo.departure);
                            moveVector = moveVector.rotateVector( 180 * rotateFraction);
                        }
                    }
                }

                const arrowAngle = Math.atan2(moveVector.y, moveVector.x)  * (180 / Math.PI)

                const arrowDiffStart = startStationCenter.subtract(arrowCenter);

                // Трансформация чтобы чтобы поставить Arrow на текущую станцию в нужном направлении
                var startStationTransform = `rotate(${arrowAngle}, ${startStationCenter.x}, ${startStationCenter.y}) translate(${arrowDiffStart.x}, ${arrowDiffStart.y})`;
                
                if (currentSeconds > currentStationInfo.departure && currentStationID != nextStationID)
                {
                    const tripSeconds = nextStationInfo.arrival - currentStationInfo.departure;
                    const movingSeconds =  currentSeconds - currentStationInfo.departure;
                    const tripFraction = movingSeconds / tripSeconds;

                    var movingTransform = `translate(${tripFraction * moveVector.x}, ${tripFraction * moveVector.y})`;

                    startStationTransform = `${movingTransform} ${startStationTransform}`;

                    routeArrow.style.fill = "#231f20";
                }
                else if (currentStationID != nextStationID)
                {
                    routeArrow.style.fill = "#23ff20";
                }

                routeArrow.setAttribute('transform', startStationTransform);
            
                routeArrow.style.visibility = 'visible';
            });
        });
    }

    currentSeconds += 2;

    setTimeout(updateTrainsPositions, 100);
}

function clearElementFill(element)
{
    element.style.fill = "none";
}

function closePopup()
{
    var popupContainter = document.getElementById("popupDisplay");
    popupContainter.innerHTML = "";
    popupContainter.style.display = "none";    
}

function handleStationClick(stationID)
{
    const stationClickZone = svgElement.getElementById(SVG.getStationClickZoneID(stationID));

    stationClickZone.style.fill = "#D3D3D3";

    setTimeout(clearElementFill, 100, stationClickZone);


    fetch("./forms/schedule_popup/schedule_popup.html")
        .then(response => response.text())
        .then(html => 
        {
            
            const popupDisplay = document.getElementById("popupDisplay");

            let shadowRoot = popupDisplay.shadowRoot;
            if (!shadowRoot) {
              // Если shadowRoot не существует, создаем его
              shadowRoot = popupDisplay.attachShadow({ mode: 'open' });
            }

            shadowRoot.innerHTML = html; // Вставляем содержимое
            popupDisplay.style.display = "block";

            shadowRoot.getElementById("closePopupBtn").addEventListener("click", closePopup);
        
            // Удаляем предыдущие скрипты
            const existingScripts = document.querySelectorAll("#popupDisplay script");
            existingScripts.forEach(script => script.remove());
        
            // Выполняем новые скрипты
            const newScripts = popupDisplay.querySelectorAll("script");
            newScripts.forEach(script => {
              const newScript = document.createElement("script");
              newScript.textContent = script.textContent;
              document.body.appendChild(newScript);
            });   
            
            SchedulePopup.loadDateStationSchedule(shadowRoot, stationID, schedule, new Date(), lines, stations);
        });
}

function handleSchemaClick(event)
{
    const clickPoint = new Geometry.Point(event.pageX, event.pageY);
    
    if (stations == null)
    {
        console.log("handleSchemaClick stations is not loaded yet");
        return;
    }

    let clickStationID;
    Object.keys(stations).forEach((stationID) => 
    {
        const stationRect = SVG.getSVGElementDocumentRect(svgElement, SVG.getStationClickZoneID(stationID), false);

        if (stationRect != null && stationRect.contains(clickPoint))
        {
            clickStationID = stationID;
            return;
        }
    });

    if (clickStationID)
    {
        handleStationClick(clickStationID);
    }
}

function loadSchemaImage()
{
    const imgSrc = './media/Схема метрополитен Харьков.svg';

    fetch(imgSrc)
      .then(response => response.text())
      .then(svgText => 
        {
        const parser = new DOMParser();
        const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
        svgElement = svgDoc.querySelector('svg');

        
        document.getElementById('schemaContainer').appendChild(svgElement);
    
        svgElement.addEventListener('click', handleSchemaClick);
        // Теперь можно получить доступ к элементам внутри SVG
        arrowSvgElement = svgElement.getElementById('arrow');
        if (arrowSvgElement) 
        {
            arrowSvgElement.style.visibility = 'hidden'; 
        }

        updateTrainsPositions();
      })
      .catch(error => console.error('Error loading SVG:', error));    
}



document.addEventListener("DOMContentLoaded", () => 
  {
    loadLines();
    loadRoutes();
    loadStations();
    loadSchedule();
    loadSchemaImage();

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

var arrowSvgElement = null;
var routesArrowSVG = {};
var svgElement = null;

var currentSeconds = Utils.secondsInHour * 16 + Utils.secondInMinute * 20 + 35;

const selectedLineID = 1;
var lines = null;
var schedule = null;
var stations = null;
var routes = null;