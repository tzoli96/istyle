define([
], function () {
  return {
    async getData(url) {
      const response = await fetch(url, {
        method: 'GET',
        mode: 'no-cors',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
      });
    
      return response.json();
    },
  }
});
