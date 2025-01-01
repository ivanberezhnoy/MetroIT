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

export function loadRoutes()
{
  return  processPromiseResult(fetch('./api/api.php?action=getRoutes')).then(jsonData => jsonData);
}

export function loadSchedule()
{
  return processPromiseResult(fetch('./api/api.php?action=getRoutesSchedule')).then(jsonData => jsonData);  
}

export function loadStations()
{
  return processPromiseResult(fetch('./api/api.php?action=getStations')).then(jsonData => jsonData);  
}