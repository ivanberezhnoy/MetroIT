export class Point 
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

    rotateVector(degrees)
    {
      const radians = degrees * (Math.PI / 180);

      // Применяем матрицу поворота
      const xNew = this.x * Math.cos(radians) - this.y * Math.sin(radians);
      const yNew = this.x * Math.sin(radians) + this.y * Math.cos(radians);

      return new Point(xNew, yNew);
    }
 }

 export class Rect {
    constructor(param1, param2, param3, param4) 
    {
      if (param1 && param2 && param3 && param4)
      {
        this.x = param1;
        this.y = param2;
        this.width = param3;
        this.height = param4;        
      }
      else
      {
        this.x = param1.x;
        this.y = param1.y;
        this.width = param1.width;
        this.height = param1.height;
      }
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

    moveToVector(vector)
    {
      return new Rect(this.x + vector.x, this.y + vector.y, this.width, this.height);
    }
  }



  