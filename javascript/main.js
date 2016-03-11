$(document).ready(function() {
  console.log(friendlyTime(Date.parse("January 1,2016 17:30") - Date.parse("January 1,2016 16:30")));
  console.log(friendlyTime(Date.parse("January 1,2017 14:30") - Date.parse("January 1,2016 11:36")));
  console.log(friendlyTime(Date.parse("December 22,2120 14:30") - Date.parse("January 1,2016 20:36")));
  var tasks;
  if(JSON.parse(localStorage.getItem("tasks") != null)) {
    tasks = JSON.parse(localStorage.getItem("tasks"));
  }
  if(tasks == null || tasks.length < 1) {
    $('#timers').html("<div class='row-fluid'><div class='col-md-12'><p class='no-timers'>No Timers Found</p></div></div>");
  }
  else {
    $.each(tasks,function() {
      var extra = ''
      var totalTime = 0;
      $('#timers').append("<div class='row-fluid'></div>");
      var item = $('#timers .row-fluid:last-child');
      item.append("<div class='col-md-8 description'>" + item.description + "</div>");
      if(item.stop == null) {
        totalTime = start-Date.now()
        extra = "running";
      }
      else {
        totalTime = start-stop;
      }
      item.append("<div class='col-md-8 time'>" + totalTime + "</div>");
    });
  }

  $('#startTimer').click(function() {
    tasks.push({
      start: Date.now(),
      stop: null,
      description: ''
    });
  })
});

function friendlyTime(ms) {
  var rawMinutes = Math.round(Math.round(ms/1000,0)/60,0); //turn the time into minutes
  var hours = Math.floor(rawMinutes/60); //devide into hours, floor the amount since we'll count it in minutes
  var minutes = rawMinutes%60 //find the minutes by getting the remainder after hours division
  if(hours < 10) {
    hours = "0"+hours //if hours is less then 10 we'll add a leading zero to make it look nice
  }
  if(minutes < 10) {
    minutes = "0"+minutes //we'll add the same leading zero to minutes.
  }
  hours = hours.toString();
  if(hours.length > 3) {
    //add hundreds seperator, this only applies to hours since the max minutes can be is 59
    var temp = hours;
    hours = "";
    /**
      we've applied the hours figure to temp so we can refill hours with the formatted string
      we'll work backwards through the string, 3 characters at a time adding a comma to the beginning
      after that we'll take the difference that wasn't accounted for in our loop and add it to the beginning
      if there is no difference we'll parse the comma off
    **/
    for(a = temp.length;a >= 3;a-=3) {
      hours = ","+temp.substring(a-3,a)+hours;
    }
    if(temp.length%3 == 0) {
      hours = hours.substring(1);
    }
    else {
      hours = temp.substring(0,temp.length%3)+hours;
    }
  }
  return hours+":"+minutes;
}
