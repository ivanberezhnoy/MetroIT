import * as Geometry from "./Geometry.js"

// Returns svg element rect in context of SVG image
export function getSVGElementRect(parent, elementID, logError = true)
{
  const startStationElement = parent.getElementById(elementID);
  if (startStationElement == null )
  {
      if (logError)
      {
        console.log("Unable to find station with ID: ${elementID}");
      }
      return null;
  }

  return new Geometry.Rect(startStationElement.getBBox());
}

// Returns svg element rect in context of html document
export function getSVGElementDocumentRect(parent, elementID, logError = true)
{
  const startStationElement = parent.getElementById(elementID);
  if (startStationElement == null )
  {
    if (logError)    
    {
        console.log("Unable to find station with ID: ${elementID}");
    }
    return null;
  }

  var result = new Geometry.Rect(startStationElement.getBoundingClientRect());

  return result.moveToVector(new Geometry.Point(window.scrollX, window.scrollY));
}


export function getStationPointID(stationID)
{
    return `Station-Point-${stationID}`;
}

export function getStationClickZoneID(stationID)
{
    return `ClickZone-Station-${stationID}`;
}