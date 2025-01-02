export default class Point 
{
    constructor(x, y) 
    {
      this.x = x;
      this.y = y;
    }
  
    distanceTo(otherPoint) 
    {
      return Math.sqrt(Math.pow(this.x - otherPoint.x, 2) + Math.pow(this.y - otherPoint.y, 2));
    }

    subtract(other) 
    {
      if (other instanceof Point) {
        // Разница по осям X и Y
        return new Point(this.x - other.x, this.y - other.y);
      }
      throw new Error('Argument must be an instance of Point');
    }    
 }

 export class Rect {
    constructor(rect) 
    {
        this.x = rect.x;
        this.y = rect.y;
        this.width = rect.width;
        this.height = rect.height;
    }    
  
    area() {
      return this.width * this.height;
    }
    
    getCenter()
    {
        return new Point(this.x + this.width / 2, this.y + this.height / 2);
    }

    contains(point) 
    {
      return (
        point.x >= this.x &&
        point.x <= this.x + this.width &&
        point.y >= this.y &&
        point.y <= this.y + this.height
      );
    }
  }

  export function getSVGElementRect(parent, elementID)
  {
    const startStationElement = parent.getElementById(elementID);
    if (startStationElement == null )
    {
        console.log("Unable to find station with ID: ${elementID}");
        return null;
    }

    return new Rect(startStationElement.getBBox());
  }

  