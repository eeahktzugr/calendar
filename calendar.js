//Parameter for id
function getParameterByName(name) {
name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

if (getParameterByName('calid')) {
  var calId = getParameterByName('calid');
}
else  {
  var calId = 'primary';
}

  // Your Client ID can be retrieved from your project in the Google
  // Developer Console, https://console.developers.google.com
  var CLIENT_ID = 'YOUR_CLIENT_ID';

  var SCOPES = ["https://www.googleapis.com/auth/calendar"];

  /**
   * Check if current user has authorized this application.
   */
   function checkAuth() {
     gapi.auth.authorize(
       {
         'client_id': CLIENT_ID,
         'scope': SCOPES.join(' '),
         'immediate': true

       }, handleAuthResult);
   }

   /**
    * Handle response from authorization server.
    *
    * @param {Object} authResult Authorization result.
    */
   function handleAuthResult(authResult) {
     var authorizeDiv = document.getElementById('authorize-div');
     if (authResult && !authResult.error) {
       // Hide auth UI, then load client library.
       authorizeDiv.style.display = 'style';
       loadCalendarApi();
     } else {
       // Show auth UI, allowing the user to initiate authorization by
       // clicking authorize button.
       authorizeDiv.style.display = 'inline';
     }
   }

   /**
    * Initiate auth flow in response to user clicking authorize button.
    *
    * @param {Event} event Button click event.
    */
   function handleAuthClick(event) {
     gapi.auth.authorize(
       {client_id: CLIENT_ID, scope: SCOPES, immediate: false},
       handleAuthResult);
     return false;
   }


  /**
   * Load Google Calendar client library. List upcoming events
   * once client library is loaded.
   */
  function loadCalendarApi() {
    gapi.client.load('calendar', 'v3', listUpcomingEvents);
    gapi.client.load('calendar', 'v3', listCalendar);
    gapi.client.load('calendar', 'v3', insertEvent);
  }

  /**
   * Print the summary and start datetime/date of the next ten events in
   * the authorized user's calendar. If no events are found an
   * appropriate message is printed.
   */
   //List calendar

   function listCalendar() {


     var request = gapi.client.calendar.calendars.get({
       'calendarId': calId,



     });

     request.execute(function(resp) {
       var summary = resp.summary;
   document.getElementById("title").innerHTML = summary;

       console.log(resp.summary);

     });

   }

  

    //Events funktion

  function listUpcomingEvents() {
     var start = new Date();
      var end = new Date();
        end.setHours(23,59,59,999);

    var request = gapi.client.calendar.events.list({
      'calendarId': calId,
      'timeMin': start.toISOString(),
      'timeMax': end.toISOString(),
      'showDeleted': false,
      'singleEvents': true,
      'maxResults': 5,
      'orderBy': 'startTime'
    });





    //Request execute // addZero (adds zero to minutes)
    request.execute(function(resp) {
      var events = resp.items;
      function addZero(i) {
          if (i < 10) {
              i = "0" + i;
          }
          return i;
      }
      //Todays Date
      var today = new Date();
      var dd = today.getDate();
          var mm = today.getMonth()+1; //January is 0!

          var yyyy = today.getFullYear();
          if(dd<10){
              dd='0'+dd
          }
          if(mm<10){
              mm='0'+mm
          }
          var today = dd+'.'+mm+'.'+yyyy;
      document.getElementById("Date").innerHTML = today;


      //Events
      if (events.length > 0) {
        var previous = null;

        for (i = 0; i < events.length; i++) {
          var event = events[i];
          var when = new Date(event.start.dateTime);
          var hs = addZero(when.getHours());
          var ms = addZero (when.getMinutes());
          var end = new Date(event.end.dateTime);
          var he = addZero(end.getHours());
          var me = addZero (end.getMinutes());
          var now = new Date();
          var hn = addZero(now.getHours());
          var mn = addZero (end.getMinutes())
          ;



          if (i == 0) {
            if (when.getTime() >= now.getTime() || end.getTime() <= now.getTime()){
              appendPre('Vapaa');
            }
          }

          if (!when) {
            when = event.start.date;
          }

          if (when.getTime() <= now.getTime() && end.getTime() >= now.getTime()){
            // This is current event
            appendPre('Varattu ' + '\n' + event.summary + '  ' +  hs + (':') +   ms + '' + (' - ') + '' +  he +  (':')  +  me + '' );

        }
        else {
            appendPre(event.summary + '  ' +  hs + (':') +   ms + '' + (' - ') + '' +  he +  (':')  +  me + '');
        }


      }
      } else {
        appendPre('Ei varauksia');
      }

    });
  }


document.getElementById("tiivis").innerHTML = summary;
  /**
   * Append a pre element to the body containing the given message
   * as its text node.
   *
   * @param {string} message Text to be placed in pre element.
   */

  function appendPre(message) {
    if (message.includes('Varattu')){
      var pre = document.getElementById('booked');
      var textContent = document.createTextNode(message + '\n'  + '\n');
  }else if (message == 'Vapaa'){
    var pre = document.getElementById('free');
    var textContent = document.createTextNode(message + '\n'  + '\n');

  } else {
    var pre = document.getElementById('bookings');
    var textContent = document.createTextNode(message + '\n' + '\n');
  }
    pre.appendChild(textContent);

  }
